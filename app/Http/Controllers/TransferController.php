<?php

namespace App\Http\Controllers;


use Validator;
use App\Balance;
use App\Report;
use Helpers;
use App\Loadcash;
use Auth;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    
    public function load_cash_web(Request $request)
    {
        $now = new \DateTime();
        $datetime = $now->getTimestamp();
        $loadcash = ['payid' => $datetime, 'user_id' => Auth::id(), 'netbank_id' => 34, 'bankref' => $datetime, 'pmethod_id' => $request->input('pmethod'), 'amount' => $request->input('amount'), 'status_id' => 0];
        Loadcash::create($loadcash);
        $data = [
            'key' => '',
            'salt' => '',
            'payurl' => 'https://secure.payu.in',
            'amount' => $request->amount,
            'mobile' => Auth::user()->mobile,
            'email' => Auth::user()->email,
            'productinfo' => 'wallet',
            'txnid' => $datetime,
            'firstname' => Auth::user()->name,
            'surl' => url(''),
            'furl' => url(''),
            'curl' => url(''),
            'pg' => 'NB',
            'bankcode' => $request->netbanking
        ];
        return view('payment.payment_process')->with('data', $data);
    }

    public function success(Request $request)
    {
        $txnRs = array();
        //print_r($_POST);
        if (!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                $txnRs[$key] = htmlentities($value, ENT_QUOTES);
            }
        }
        if (!empty($txnRs['status'])) {
            if ($txnRs['key'] == '') {
                $ipAddress = $_SERVER['REMOTE_ADDR'];
                if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
                    $ipAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
                }
                if ($txnRs['status'] == 'success') {
                    $now = new \DateTime();
                    $datetime = $now->getTimestamp();
                    $ctime = $now->format('Y-m-d H:i:s');
                    $merc_hash_vars_seq = explode('|', "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10");
                    //generation of hash after transaction is = salt + status + reverse order of variables
                    $merc_hash_vars_seq = array_reverse($merc_hash_vars_seq);
                    $txnid = $txnRs['txnid'];
                    $amount = $txnRs['amount'];
                    $loadcahses = Loadcash::where('payid', $txnid)->firstOrFail();
                    if ($loadcahses->amount == $amount) {
                        if ($amount <= 5000) {
                            $bankref = $txnRs['bank_ref_num'];
                            $rules = array('amount' => 'required',
                                'txnid' => 'required|unique:reports'
                            );
                            $validator = Validator::make($request->all(), $rules);
                            if ($validator->fails()) {
                                return redirect('home')
                                    ->withErrors($validator)
                                    ->withInput();
                                // redirect our user back to the form with the errors from the validators
                            } else {

                                $gcom = 0;
                                $famount = $amount - $gcom;
                                //return $request->all();
                                $userdetail = User::find($loadcahses->user_id);
                                $userbalance = $userdetail->balance->user_balance;
                                $balancenew = $userbalance + $famount;
                                $report_id = Report::insertGetId(['created_at' => $ctime, 'profit' => 0, 'number' => $userdetail->mobile, 'txnid' => $txnid, 'detail' => $bankref, 'api_id' => 1, 'provider_id' => 203, 'user_id' => $loadcahses->user_id, 'amount' => $amount, 'total_balance' => $balancenew, 'status_id' => 5]);
                                Balance::where('user_id', $loadcahses->user_id)->increment('user_balance', $famount);
                                $mssage = "Successfully Rs " . $txnRs['amount'] . " , added in Pay2All wallet, Thanks...";
                                $cyber = new \App\Library\Email;
                                $data['amount'] = $amount;
                                $data['id'] = $txnid;
                                $data['balance'] = $balancenew;
                                $now = new \DateTime();
                                $datetime = $now->getTimestamp();
                                $data['ctime'] = $now->format('Y-m-d H:i:s');
                                $data['parent_name'] = 'Pay2All';
                                $data['name'] = $userdetail->name;
                                $user = $userdetail;
                                $mobile = $userdetail->mobile;
                                Helpers::send_sms_msg($mobile, $mssage);
                                return response()->json(['status' => 1, 'message' => 'Successfully Added in Pay2All Wallet']);
                            }
                        } else {
                            return "Please fill Payment Request From in pay2all control panel";
                        }
                    } else {
                        //print_r($_POST);
                        return "Wrong Detail";
                    }
                }
            }
        } else {
            return "Sorry, Wrong Detail";
        }
    }

   
}

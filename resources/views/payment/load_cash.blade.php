@extends('layouts.template')

@section('content')



    <script>

        function submitgateway() {
            $("#btnn").text("Processing");
            $("#submitgateway").click();
        }

    </script>

    <section class="hero is-primary">
        <div class="hero-body">

            <div class="container profile">
                <div class="box">
                    <!-- Main container -->
                    <nav class="level">
                        <!-- Left side -->
                        <div class="level-left">
                            <div class="level-item">
                                <p style="color: black" class="subtitle is-5">
                                    Balance :
                                    <strong style="color: black"><i class="fa fa-inr"></i> {{ number_format(Auth::user()->balance->user_balance,2) }}
                                    </strong>
                                </p>
                            </div>
                            <div class="level-item">
                                {{ Form::open(array('url' => 'load_cash', 'method' => 'POST', 'class' => 'form-light')) }}

                                {{ csrf_field() }}
                                <INPUT TYPE="hidden" NAME="product" value="NSE">
                                <INPUT TYPE="hidden" NAME="TType" value="NBFundTransfer">

                                <INPUT TYPE="hidden" NAME="clientcode" value="007">
                                <INPUT TYPE="hidden" NAME="AccountNo"
                                       value="{{ 'CERESI'.Auth::id() }}">

                                <INPUT TYPE="hidden" NAME="ru"
                                       value="http://digital.pay2all.in/payment-gateway/success">
                                <input type="hidden" name="bookingid" value="100001"/>
                                <input name="udf1" type="hidden"
                                       value="{{ Auth::user()->name }}"/>
                                <input name="udf2" type="hidden"
                                       value="{{ Auth::user()->mobile }}"/>
                                <input name="udf3" type="hidden"
                                       value="{{ Auth::user()->email }}"/>
                                <input name="udf4" type="hidden" value="delhi"/>


                                <input style="display: none" id="submitgateway" type="submit">


                                <p class="control">
                                    <input name="amount" required class="input is-medium" type="text"
                                           placeholder="Enter Amount">
                                </p>
                                </form>
                            </div>

                            <button onclick="submitgateway()" class="button is-primary is-medium">
                                Add Money
                            </button>

                        </div>

                        <!-- Right side -->
                        <div class="level-right">
                            <button class="button is-info is-medium">
                                Request Payment
                            </button>
                        </div>
                    </nav>
                </div>
            </div>
        </div>

    </section>

    <br>

    <section>
        <div id="app" class="container">

            <nav class="panel">
                <p class="panel-heading">
                    Recent Transactions
                </p>
                <div class="box">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Date &amp; Time</th>
                            <th>Amount</th>
                            <th>Balance</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in basketItems">
                            <td>@{{ item.service }}</td>
                            <td>@{{ item.provider }}-@{{ item.number }} <br>@{{ item.id }}</td>
                            <td>@{{ item.created_at }}</td>
                            <td>@{{ item.amount }}</td>
                            <td>@{{ item.total_balance }}</td>
                            <td>
                                <a :class="item.label">@{{ item.status }}</a>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                    <div class="container has-text-centered">
                        <a @click.prevent="changePage(pagination.current_page + 1)" class="button is-primary">
                            <span class="icon is-small">
                                 <i class="fa fa-align-center"></i>
                            </span>
                            <span>View More</span>
                        </a>

                    </div>

                </div>
            </nav>
        </div>
    </section>





@endsection
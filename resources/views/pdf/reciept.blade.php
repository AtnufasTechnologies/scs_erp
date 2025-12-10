<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Payment Reciept</title>
    <link rel="shortcut icon" href="{{asset('web/img/salesion.png')}}" type="image/x-icon">

    <style>
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;

        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 22px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            font-size: 13px;
            text-align: center;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {

            font-size: 12px;
            border-bottom: 1px solid #eee;

        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /** RTL **/
        .invoice-box.rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .invoice-box.rtl table {
            text-align: right;
        }

        .invoice-box.rtl table tr td:nth-child(2) {
            text-align: left;
        }

        .text-caps {
            text-transform: uppercase;
        }

        .font-16b {
            font-size: 16px;
            font-weight: 700;
        }
    </style>
</head>

<body>



    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr>



                <td colspan="3" class="text-center">
                    <img src="{{public_path('web/img/salesion.png')}}" alt="logo">

                </td>
                <td colspan="3">
                    <h3>Salesian College (Autonomous)</h3>
                    <label> Sonada and Siliguri</label><br>
                    <label for="" class="mx-3">Timestamp :{{$record->created_at}}</label><br>

                    <label>{{$record->registration->campusInfo->name}} -
                        {{$record->registration->programInfo->name}} <br>
                </td>
                <td>

                </td>
            </tr>

            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>

                            <td>

                                <label class="text-caps font-16b"> {{$record->name}}</label><br>
                                <label for="">MobileNo. {{$record->phone}} </label><br>
                                <label>Email - {{$record->email}} </label><br>

                            </td>


                        </tr>
                    </table>
                </td>
            </tr>


        </table>

        <hr>
        <center><b>TRANSACTION RECIEPT</b></center>
        <div style="height: 200px;">
            <label>Application Form No# {{$record->application_id}}</label><br>
            <label for="">{{$record->dept->name}}</label><br>
            <label for="">{{$record->course->name}}</label><br>
            <label for="">PAYMENT ID# - {{$record->payment_gateway_id}}</label><br>
            <label for="">Amount - <b>{{$record->captured_amount}}/-</b> </label><br>
            <label for="">Status - {{$record->payment_gateway_status}}</label>
        </div>
        <hr>
        <center>
            <label for="">This is a Computer Generated Reciept</label> <br>
            <label for="">Contact Admission Office for any queries or issues</label>

        </center>



    </div>

</body>

</html>
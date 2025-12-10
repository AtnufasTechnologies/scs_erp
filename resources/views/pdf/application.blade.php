<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>{{$record->application_id}}</title>
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
                    <label>Admission Form No# {{$record->application_id}}</label><br>
                    <label>{{$record->registration->campusInfo->name}} {{$record->registration->programInfo->name}} - {{$record->dept->name}}</label>
                    <br>{{$record->course->name}}
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
                            <td>
                                <img src="{{Storage::disk('s3')->url('profile/'.$record->pic)}}"
                                    style="width: 100%; max-width: 150px" class="thumbnail mt-1" />
                            </td>


                        </tr>
                    </table>
                </td>
            </tr>


        </table>




        <table cellpadding="4" cellspacing="4">


            <tr class="heading">

                <td> DOB : {{date('d-m-Y',strtotime($record->dob))}}</td>
                <td>Blood Group - {{$record->bloodgroup}}</td>
                <td>Gender - {{$record->gender}}</td>
                <td>Caste - {{$record->caste}}</td>

            </tr>

        </table>
        </td>
        </tr>

        </table>


        <table cellpadding="4" cellspacing="4">
            <tr class="item">

                <td colspan="2">
                    <table>
                        <tr class="heading">

                            <td>Nationality - {{$record->registration->countryInfo->name}}</td>
                            <td>Religion - {{$record->religionInfo->name}}</td>
                            <td>Mother Tongue - {{$record->mothertongue}}</td>
                            <td>Phys Challenged - {{$record->physically_challanged}}</td>

                        </tr>


                    </table>
                </td>
            </tr>
        </table>



        <table cellpadding="3" cellspacing="3">
            <label for="">Family Details</label>

            <tr class="heading">

                <td>FatherName - {{$record->fname}}</td>
                <td>Occupation - {{$record->foccupation}}</td>
                <td>Contact - {{$record->fcontact}}</td>
            </tr>

            <tr class="heading">

                <td>MotherName - {{$record->mname}}</td>
                <td>Occupation - {{$record->moccupation}}</td>
                <td>Contact - {{$record->mcontact}}</td>
            </tr>

            <tr class="heading">

                <td>Guardian Name - {{$record->gname}}</td>
                <td>Guardian Contact - {{$record->gcontact}}</td>
                <td>Monthly Income - {{$record->monthly_income}}</td>
            </tr>



        </table>

        <table cellpadding="2" cellspacing="2">
            <tr class="item">

                <td>
                    <label for="">Permanent Address</label>
                    <p> {{$record->permanent_address}} <br>{{$record->per_pin}}</p>
                </td>


                <td>
                    <label for="">Local Address</label>
                    <p> {{$record->local_address}} <br>{{$record->loc_pin}}</p>
                </td>
            </tr>
        </table>

        @if($record->registration->application_type == 2)
        <table cellpadding="2" cellspacing="2">
            <label for="">Educational Details</label>

            <tr class="item">

                <td colspan="2">

            <tr class="item">
                <td>Last Studied Institution - {{$record->lastinstname}}</td>

            </tr>
            <tr class="item">

                <td> Semester I - {{$record->sgpa1}}</td>

            </tr>
            <tr class="item">

                <td> Semester II - {{$record->sgpa2}}</td>

            </tr>
            <tr class="item">

                <td> Semester III - {{$record->sgpa3}}</td>

            </tr>
            <tr class="item">

                <td> Semester IV - {{$record->sgpa4}}</td>

            </tr>
            <tr class="item">

                <td> Semester V - {{$record->sgpa5}}</td>

            </tr>
            <tr class="item">

                <td> Semester VI - {{$record->sgpa6}}</td>

            </tr>

            </td>
            </tr>

        </table>



        @else
        <table cellpadding="2" cellspacing="2">
            <label for="">Educational Details</label>

            <tr class="item">

                <td colspan="2">

            <tr class="item">
                <td>Institution Class X</td>
                <td>Institution Class XII </td>
            </tr>
            <tr class="heading">


                <td> {{$record->institution10}}</td>

                <td> {{$record->institution12}}</td>

            </tr>



            </td>
            </tr>

        </table>


        <table cellpadding="2" cellspacing="2">
            <tr class="heading">

                <td>
                    Subjects
                </td>
                <td>
                    Score
                </td>


            </tr>
            <label for="">Class XII Marksheet</label>
            <tr>
                <td>
                    {{$record->sub1}}
                </td>
                <td>
                    {{$record->score1}} / 100
                </td>

            </tr>
            <tr>
                <td>
                    {{$record->sub2}}
                </td>
                <td>
                    {{$record->score2}} / 100
                </td>

            </tr>
            <tr>
                <td>
                    {{$record->sub3}}
                </td>
                <td>
                    {{$record->score3}} / 100
                </td>

            </tr>
            <tr>
                <td>
                    {{$record->sub4}}
                </td>
                <td>
                    {{$record->score4}} / 100
                </td>

            </tr>
            <tr>
                <td>
                    {{$record->sub5}}
                </td>
                <td>
                    {{$record->score5}} / 100
                </td>

            </tr>
        </table>

        @endif

        <hr>
        <div style="height: 200px;">
            <label for="">FOR OFFICIAL USE ONLY.....</label>
        </div>



    </div>
    <label for="">PAYMENT ID# - {{$record->payment_gateway_id}}</label>
    <label for=""> {{$record->msg}}</label>
</body>

</html>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>UG Application - {{$data->application_code}}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm 8mm 10mm 8mm;
        }

        html,
        body {
            width: 210mm;
            height: 297mm;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #222;
            background: #f8fafc;
        }

        .pdf-container {
            width: 100%;
            max-width: 194mm;
            margin: 0 auto;
            padding: 8px 10px 8px 10px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
        }

        h2,
        h3,
        h4 {
            margin: 0 0 2px 0;
            font-size: 15px;
            color: #1a237e;
            letter-spacing: 0.5px;
        }

        .header-table td {
            border: none !important;
            padding-bottom: 2px;
        }

        .header-table img {
            margin-bottom: 2px;
        }

        .header-table .college-title {
            font-size: 16px;
            font-weight: bold;
            color: #0d47a1;
        }

        .header-table .subtitle {
            color: #333;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #b0bec5;
            padding: 3px 6px;
            font-size: 9px;
        }

        th {
            background: #e3eafc;
            color: #1a237e;
            font-weight: 600;
            letter-spacing: 0.2px;
        }

        .section-title {
            background: linear-gradient(90deg, #e3eafc 0%, #f8fafc 100%);
            font-weight: bold;
            padding: 5px 8px;
            font-size: 10.5px;
            color: #0d47a1;
            border-radius: 4px 4px 0 0;
            margin-bottom: 0;
            border: 1px solid #b0bec5;
            border-bottom: none;
        }

        .label {
            font-weight: bold;
        }

        .photo {
            max-width: 22mm;
            max-height: 28mm;
            border: 1px solid #b0bec5;
            border-radius: 4px;
        }

        tr:last-child td {
            border-bottom: 1.5px solid #b0bec5;
        }

        tr:first-child th {
            border-top: 1.5px solid #b0bec5;
        }

        @media print {

            html,
            body {
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 0;
                background: #fff;
            }

            .pdf-container {
                max-width: 194mm;
                margin: 0 auto;
                padding: 8px 10px 8px 10px;
                border-radius: 8px;
                background: #fff;
                box-shadow: none;
            }

            table {
                page-break-inside: avoid;
            }

            .section-title {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="pdf-container">
        <table class="header-table">
            <tr>
                <td colspan="2" style="border:none; padding-bottom: 5px;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="border: none; width: 15%;">
                                <img src="{{ asset('admin/images/logo.png') }}" alt="logo" style="height: 50px;">
                            </td>
                            <td style="border: none; width: 70%;">
                                <div class="college-title">Salesian College (Autonomous)</div>
                                <div class="subtitle">Sonada and Siliguri</div>
                                <div class="subtitle">UG - Online Application Form</div>
                                <div class="subtitle ">{{$data->stdCourseMaster->name}} </div>
                            </td>
                            <td style="border: none; width: 15%; text-align: right; vertical-align: top;">
                                @if(isset($data->photo))
                                <img src="{{ Storage::disk('s3')->url($data->photo) }}" class="photo" alt="Photo" />
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="border: none; font-size: 9px;"><b>Form No:</b> {{$data->application_code}}</td>
                <td style="border: none; font-size: 9px; text-align: right;"><b>Date:</b> {{date('d-m-Y', strtotime($data->created_at))}}</td>
            </tr>
            <tr>
                <td style="border: none; font-size: 9px;" colspan="2"><b>Batch:</b> {{$data->registrationmaster->batch}} </td>
            </tr>
        </table>

        <div class="section-title">Personal Details</div>
        <table>
            <tr>
                <th style="width: 20%;">Name</th>
                <td style="width: 30%;">{{$data->registrationmaster->first_name}} {{$data->registrationmaster->last_name}}</td>
                <th style="width: 20%;">Mobile No</th>
                <td style="width: 30%;">{{$data->registrationmaster->mobile_no}}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{$data->registrationmaster->mail_id}}</td>
                <th>Date of Birth</th>
                <td>{{date('d-m-Y', strtotime($data->dob))}}</td>
            </tr>
            <tr>
                <th>Gender</th>
                <td>{{$data->gender}}</td>
                <th>Blood Group</th>
                <td>{{$data->bloodgroupmaster->name}}</td>
            </tr>
            <tr>
                <th>Religion</th>
                <td>{{$data->religionmaster->name}}</td>
                <th>Mother Tongue</th>
                <td>{{$data->mothertongue}}</td>
            </tr>
            <tr>
                <th>Physically Challenged</th>
                <td>{{$data->phychallenged}}</td>
                <th>Caste</th>
                <td>{{$data->caste}}</td>
            </tr>
            <tr>
                <th>Have Laptop/Desktop ?</th>
                <td>@if($data->has_laptop == 1) Yes @else No @endif</td>
                <th>Reside at Tea Estate ?</th>
                <td>@if($data->from_teaestate == 1) Yes @else No @endif</td>
            </tr>
            <tr>
                <th>Nationality</th>
                <td>{{$data->registrationmaster->countrymaster->name}}</td>
                <th>Campus</th>
                <td>{{$data->registrationmaster->campusmaster->name}}</td>
            </tr>
            <tr>
                <th>Department</th>
                <td>{{$data->academicDeptMaster->title}}</td>
                <th>Course</th>
                <td>{{$data->stdCourseMaster->name}}</td>
            </tr>
        </table>

        <div class="section-title">Parent / Guardian Details</div>
        <table>
            <tr>
                <th style="width: 20%;">Father's Name</th>
                <td style="width: 30%;">{{$data->father_name}}</td>
                <th style="width: 20%;">Father's Occupation</th>
                <td style="width: 30%;">{{$data->father_occupation}}</td>
            </tr>
            <tr>
                <th>Father's Contact</th>
                <td>{{$data->father_contact}}</td>
                <th>Father's Qualification</th>
                <td>{{$data->father_qualification}}</td>
            </tr>
            <tr>
                <th>Mother's Name</th>
                <td>{{$data->mother_name}}</td>
                <th>Mother's Occupation</th>
                <td>{{$data->mother_occupation}}</td>
            </tr>
            <tr>
                <th>Mother's Contact</th>
                <td>{{$data->mother_contact}}</td>
                <th>Mother's Qualification</th>
                <td>{{$data->mother_qualification}}</td>
            </tr>
            <tr>
                <th>Guardian's Name</th>
                <td>{{$data->guardian_name}}</td>
                <th>Guardian's Contact</th>
                <td>{{$data->guardian_contact}}</td>
            </tr>
            <tr>
                <th>Family Monthly Income</th>
                <td colspan="3">{{$data->income}}</td>
            </tr>
        </table>

        <div class="section-title">Address Details</div>
        <table>
            <tr>
                <th style="width: 20%;">Permanent Address</th>
                <td style="width: 30%;">{{$data->permanent_address}}</td>
                <th style="width: 20%;">District</th>
                <td style="width: 30%;">{{$data->district}}</td>
            </tr>
            <tr>
                <th>City</th>
                <td>{{$data->city}}</td>
                <th>Pincode</th>
                <td>{{$data->pincode}}</td>
            </tr>
            <tr>
                <th>Local Address</th>
                <td>{{$data->local_address}}</td>
                <th>Local District</th>
                <td>{{$data->local_district}}</td>
            </tr>
            <tr>
                <th>Local City</th>
                <td>{{$data->local_city}}</td>
                <th>Local Pincode</th>
                <td>{{$data->local_pincode}}</td>
            </tr>
        </table>

        <div class="section-title">Academic Details - Class X</div>
        <table>
            <tr>
                <th style="width: 20%;">Institution</th>
                <td style="width: 30%;">{{$data->institution10}}</td>
                <th style="width: 20%;">Roll No</th>
                <td style="width: 30%;">{{$data->rollno10}}</td>
            </tr>
            <tr>
                <th>Board</th>
                <td>{{$data->board10}}</td>
                <th>Year of Passing</th>
                <td>{{$data->passingyear10}}</td>
            </tr>
            <tr>
                <th>Certificate</th>
                <td colspan="3">@if(isset($data->certificate10)) Yes @else No @endif</td>
            </tr>
            <tr style="background: #f9f9f9;">
                <th style="text-align: center;">Subject</th>
                <th style="text-align: center;">Score (100)</th>
                <th style="text-align: center;">Subject</th>
                <th style="text-align: center;">Score (100)</th>
            </tr>
            <tr>
                <td>{{$data->subject10_1}}</td>
                <td style="text-align: center;">{{$data->score10_1}}</td>
                <td>{{$data->subject10_4}}</td>
                <td style="text-align: center;">{{$data->score10_4}}</td>
            </tr>
            <tr>
                <td>{{$data->subject10_2}}</td>
                <td style="text-align: center;">{{$data->score10_2}}</td>
                <td>{{$data->subject10_5}}</td>
                <td style="text-align: center;">{{$data->score10_5}}</td>
            </tr>
            <tr>
                <td>{{$data->subject10_3}}</td>
                <td style="text-align: center;">{{$data->score10_3}}</td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <div class="section-title">Academic Details - Class XII</div>
        <table>
            <tr>
                <th style="width: 20%;">Institution</th>
                <td style="width: 30%;">{{$data->institution12}}</td>
                <th style="width: 20%;">Roll No</th>
                <td style="width: 30%;">{{$data->rollno12}}</td>
            </tr>
            <tr>
                <th>Board</th>
                <td>{{$data->board12}}</td>
                <th>Year of Passing</th>
                <td>{{$data->passingyear12}}</td>
            </tr>
            <tr>
                <th>Certificate</th>
                <td colspan="3">@if(isset($data->certificate12)) Yes @else No @endif</td>
            </tr>
            <tr style="background: #f9f9f9;">
                <th style="text-align: center;">Subject</th>
                <th style="text-align: center;">Score (100)</th>
                <th style="text-align: center;">Subject</th>
                <th style="text-align: center;">Score (100)</th>
            </tr>
            <tr>
                <td>{{$data->subject12_1}}</td>
                <td style="text-align: center;">{{$data->score12_1}}</td>
                <td>{{$data->subject12_3}}</td>
                <td style="text-align: center;">{{$data->score12_3}}</td>
            </tr>
            <tr>
                <td>{{$data->subject12_2}}</td>
                <td style="text-align: center;">{{$data->score12_2}}</td>
                <td>{{$data->subject12_4}}</td>
                <td style="text-align: center;">{{$data->score12_4}}</td>
            </tr>
        </table>

        <div class="section-title">Payment Details</div>
        <table>
            <tr>
                <th>Payment ID</th>
                <td>{{$data->payment_gateway_ref}}</td>
            </tr>
            <tr>
                <th>Payment Status</th>
                <td>{{$data->payment_gateway_status}}</td>
            </tr>
            <tr>
                <th>Computer Generated </th>
                <td>
                    {{ now()->format('d-m-Y H:i:s') }}
                </td>
            </tr>

        </table>

        <div style="margin-top:8px; text-align:center; color:#999; font-size: 9px;">FOR OFFICIAL USE ONLY</div>
    </div>
</body>

</html>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>PG Application - {{$data->application_code}}</title>
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
            font-size: 12px;
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
                                <img src="{{ asset('admin/images/logo.png') }}" alt="logo" style="height: 90px;">
                            </td>
                            <td style="border: none; width: 70%;">
                                <div class="college-title">Salesian College (Autonomous)</div>
                                <div class="subtitle">Sonada and Siliguri</div>
                                Email: admissionenquiry@salesiancollege.net <br> Phone: +91 99334 02478 / 0353 254 5622
                                <br><br>
                                <div class="subtitle">PG - Online Application Form ({{$data->registrationmaster->campusmaster->name}})</div>

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
                <td></td>
                <td style="border: none; text-align: right;">
                    <button onclick="window.print()" style="padding: 6px 12px; background: #0d47a1; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 10px; font-weight: 600;">Print</button>
                </td>
            </tr>
            <tr>
                <td style="border: none; font-size: 9px;"><b>Form No:</b> {{$data->application_code}}</td>
                <td style="border: none; font-size: 9px; text-align: right;"><b>Date:</b> {{date('d-m-Y', strtotime($data->created_at))}}</td>
            </tr>
            <tr>
                <td style="border: none; font-size: 9px;" colspan="2"><b>Batch:</b> {{$data->registrationmaster->batch}} </td>
            </tr>
            <tr>
                <td style="border: none; font-size: 9px;" colspan="2"><b>Applied For:</b> {{$data->stdCourseMaster->name}}</td>
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

            @if($data->registrationmaster->country == 101)
            <tr>
                <th>Adhaar</th>
                <td>{{$data->adhaar }}</td>

            </tr>
            @else
            <tr>
                <th>National Id Proof</th>
                <td>{{$data->national_id_proof != null ? 'Yes' : 'No' }}</td>

            </tr>
            @endif
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
            <tr>
                <th>State </th>
                <td>{{$data->state}}</td>
                <th>Local State</th>
                <td>{{$data->local_state}}</td>
            </tr>
        </table>



        <div class="section-title">College Details</div>
        <table>
            <tr>
                <th style="width: 20%;">College</th>
                <td style="width: 30%;">{{$data->college_name}}</td>
                <th style="width: 20%;">University</th>
                <td style="width: 30%;">{{$data->university_name}}</td>
            </tr>
            <tr>
                <th>Reg No</th>
                <td>{{$data->graduating_rollno}}</td>
                <th>Graduating Year</th>
                <td>{{$data->graduating_year}}</td>
            </tr>
            <tr>
                <th> Marksheet Uploaded</th>
                <td colspan="3">@if(isset($data->college_marksheet)) Yes @else No @endif</td>
            </tr>
            <tr>
                <th>Semester 1</th>
                <td>{{$data->sgpa1}}</td>
                <th>Semester 2</th>
                <td>{{$data->sgpa2}}</td>

            </tr>

            <tr>
                <th>Semester 3</th>
                <td>{{$data->sgpa3}}</td>
                <th>Semester 4</th>
                <td>{{$data->sgpa4}}</td>

            </tr>

            <tr>
                <th>Semester 5</th>
                <td>{{$data->sgpa5}}</td>
                <th>Semester 6</th>
                <td>{{$data->sgpa6}}</td>

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
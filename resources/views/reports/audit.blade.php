@extends('reports.layout')
@section('content')

    <div id="header">
        <table width="100%" style="border: 0; border-collapse: collapse;">
            <tr>
                <td width="50%" style="text-align: left; border: 0; padding: 0;">
                    <b>AUDIT REPORT</b>
                </td>
                <td width="50%" style="text-align: right; border: 0; padding: 0;">
                    <b>CONFIDENTIAL</b>
                </td>
            </tr>
        </table>


    </div>
    <div id="footer">
        <table width="100%" style="border: 0; border-collapse: collapse;">
            <tr>
                <td width="50%" style="text-align: left; border: 0; padding: 0;">
                    Created on {{ date('Y-m-d') }}
                </td>
                <td width="50%" style="text-align: right; border: 0; padding: 0;">
                    <p class="page" style="margin-right: 5px"><?php $PAGE_NUM ?></p>
                </td>
            </tr>
        </table>
    </div>
    <div id="content">


        <div style="margin-top: 100px">
        <center><h1>Audit Report</h1></center>

        <br><br>
        @php
            $path = public_path('img/logo.png'); // Adjust this path if needed
            $imageData = base64_encode(file_get_contents($path));
            $mimeType = mime_content_type($path);
        @endphp
        <center>
        <img style="max-width: 40%" src="data:{{ $mimeType }};base64,{{ $imageData }}" alt="Logo">
        </center>
        <br><br>
        <center><h2>{{ $audit->title }}</h2></center>
        <center>Audit Date: {{ $audit->start_date }} - {{ $audit->end_date }}</center>
        </div>




        <div class="page-break"></div>

        <center><h2>Audit Controls Summary</h2></center>
        <p>The following table depicts all the controls tested in this audit. Each completed
        control in the table was assessed for both applicability of the control and the effectiveness
        of it's implementation. Evidence was collected during the period of performance and includes
        interviews, observations, and document reviews.
        </p>

        <table class="table table-striped" width="100%" border="1">
            <thead>
            <tr>
                <th>Control</th>
                <th>Status</th>
                <th>Applicability</th>
                <th>Effectiveness</th>
            </tr>
            </thead>
            <tbody>
            @foreach($auditItems as $item)
                <tr>
                    <td>{{$item->auditable->code}}</td>
                    <td class="{{ strtolower(str_replace(' ', '', $item->status->value)) }}">{{$item->status->value }}</td>
                    <td class="{{ strtolower(str_replace(' ', '', $item->applicability->value)) }}">{{ $item->applicability->value }}</td>
                    <td class="{{ strtolower(str_replace(' ', '', $item->effectiveness->value)) }}">{{ $item->effectiveness->value }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <br><br>
            <div class="page-break"></div>
        <center><h2>Audit Controls Details</h2></center>

        @foreach($auditItems as $item)

            <table border="1" width="100%">
                <tr>
                    <td style="width:25%;">{{ $item->auditable->code }}</td>
                    <td style="width:25%;"
                        class="{{ strtolower(str_replace(' ', '', $item->status->value)) }}">{{$item->status->value}}</td>
                    <td style="width:25%;"
                        class="{{ strtolower(str_replace(' ', '', $item->applicability->value)) }}">{{$item->applicability->value}}</td>
                    <td style="width:25%;"
                        class="{{ strtolower(str_replace(' ', '', $item->effectiveness->value)) }}">{{$item->effectiveness->value}}</td>
                </tr>
                <tr>
                    <td style="width:25%;">Standard</td>
                    <td colspan="3">{{ $item->auditable->standard->name }}</td>
                </tr>
                <tr>
                    <td>Control</td>
                    <td colspan="3">{{ $item->auditable->code }} {{ $item->auditable->title }}
                        <br>{!! $item->auditable->description !!}</td>
                </tr>
                <tr>
                    <td>Auditor Notes</td>
                    <td colspan="3">{!! $item->auditor_notes !!}</td>
                </tr>
                <tr>
                    <td>Implementations</td>
                    <td colspan="3">
                        @foreach($item->auditable->implementations as $implementation)
                            {!!  $implementation->details  !!}
                        @endforeach


                    </td>
                </tr>
            </table>
            <br><br>
        @endforeach

    </div>
@endsection

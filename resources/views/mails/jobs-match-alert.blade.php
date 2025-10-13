<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Similar Jobs Alert</title>
        <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        <style>
            @media print {
                * {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }
            body {
                width: 600px;
                font-family: 'Libre Franklin', sans-serif;
                background: #fff;
                margin: 0 auto;
            }
            .hr-table {
                width: 100%;
                border-collapse: collapse;
            }
            .hr-table td {
                padding: 0;
                vertical-align: top;
            }
            .location-icon:before {
                display: inline-block;
                color: #7680a3;
                font-size: 16px;
                padding-right: 0;
                margin-right: 2px;
                width: 16px;
                height: 16px;
                position: relative;
                top: 3px;
                content: url(https://static.naukimg.com/s/9/121/_next/static/media/locIcon.8eb7245c.svg);
            }
            .exp:before {
                display: inline-block;
                color: var(--N600);
                font-size: 16px;
                padding-right: 0;
                margin-right: 8px;
                width: 16px;
                height: 16px;
                position: relative;
                top: 1px;
                content: url(https://static.naukimg.com/s/9/121/_next/static/media/briefcase.ebb10d53.svg);
            }
            .comp-dtls{
                padding-bottom:15px;
                display: block;
            }
            .job-location {
                padding-bottom:12px;
                display: block;
            }
            .job-details {
                padding-bottom:3px;
                display: block;
            }
        </style>
    </head>
    <body>
        <div >
            <table style="width: 100%;border-collapse: collapse;">
                <thead>
                    <tr style=>
                        <td style="padding: 20px 0;border-bottom:1px solid#0000002b;padding-bottom:10px;">
                            <div class="logo" style="width: 50%;float: left;">
                                <img class="logo-img" src="{{asset('public/uploads/email-template-new/logo.svg')}}" alt="logo" style="width: 180px;object-fit: contain;">
                            </div>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td
                            style="">
                            <p style="color: #000;font-size: 18px;font-weight: bold;margin-bottom: 10px;padding-top:5px;">
                                {{$name}}, here are the list of matched jobs according to your saved criteria.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="">
                            <a href="#" class="apply-btn" style="background-color: #113775;color: white;padding: 4px 30px;text-decoration: none;border-radius: 8px;font-weight: 500;display: inline-block;font-size: 15px;line-height: 30px;">View All Jobs</a>
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="">
                            <p style="color: #484f6d;font-size: 16px;font-weight: 500;margin: 0px;padding-top:0px;margin-bottom: 15px;">&nbsp;</p>
                        </td>
                    </tr>
                    @foreach ($jobs as $job)
                        <tr>
                            <td style="">
                                <div class="job-tuple layout-wrapper" style="position: relative; background: #ffffff; border-radius: 20px; margin-bottom: 16px; padding: 24px 24px 20px; transition: box-shadow .2s linear; border: 1px solid#d4dbe3;">
                                    <div class="row1" style="padding-bottom:5px;">
                                        <h2 style="margin:0;">
                                            <a class="title " title="{{$job['position_name']}}" href="{{env('FRONTEND_URL').'job-details/'.$job['job_no']}}" target="_blank" rel="noopener noreferrer" style="line-height: 22px; color: #275df5; max-width: 100%; display: block; font-size: 18px;
                                            font-weight: 700; text-decoration: none;">{{$job['position_name']}}</a>
                                        </h2>
                                        {{-- <span class="imagewrap " style="float: none; position: absolute; right: 24px;top: 24px; width: 50px; height: 50px;">
                                            <img style="height: 50px; width: 50px; border-radius: 14px; border: 1px solid #e5e5f2;" src="https://img.naukimg.com/logo_images/groups/v1/7300147.gif" class="logoImage" loading="lazy">
                                        </span> --}}
                                    </div>
                                    <div class=" row2" style="padding-bottom:5px;">
                                        <span class="comp-dtls">
                                            <a class="comp-name"  href="#" target="_blank" style="font-weight: 500; font-size: 14px; line-height: 18px; color: #494f6d;text-decoration:none;">
                                                {{$job['employer']['name']??'N/A'}}
                                            </a>
                                        </span>
                                    </div>
                                    <div class=" row3" style="padding-bottom:5px;">
                                        <div class="job-details ">
                                            <span class="exp" style="font-weight: 500; font-size: 14px; line-height: 18px; color: #494f6d;">{{$job['min_exp_year'].'-'.$job['max_exp_year']}} Yrs</span>
                                        </div>
                                    </div>
                                    <div class=" row4" style="padding-bottom:5px;">
                                        <div class="job-location ">
                                            <span class="location-icon" style=" font-weight: 500; font-size: 14px; line-height: 18px; color: #494f6d;">{{json_decode($job['location_city_names'], 0)[0]}}, {{json_decode($job['location_country_names'], 0)[0]}}</span>
                                        </div>
                                    </div>
                                    <div class=" row5" style="padding-bottom:5px;">
                                        <div class="job-location ">
                                            <?php
                                                // Define the two dates
                                                $date1 = new DateTime(date('Y-m-d', strtotime($job['posting_open_date'])));
                                                $date2 = new DateTime(date('Y-m-d'));

                                                // Calculate the difference
                                                $interval = $date1->diff($date2);

                                                // Get the number of days
                                                $days = $interval->days;
                                                $x = $days.' days ago';
                                                if($days > 7){
                                                    // Calculate the number of weeks (integer division)
                                                    $weeks = floor($days / 7);
                                                    $x = $weeks.' weeks ago';
                                                }
                                            ?>
                                            <span class="job-post-day " style="font-weight: 500;font-size: 14px;line-height: 18px; color: #494f6d;">{{ $x }}</span>
                                            <span class="appaly-btn ">
                                                <a href="#" target="_blank" style="font-weight: 700;font-size: 14px;line-height: 18px;color: #494f6d;color: #275df5;text-decoration: none;padding-left: 8px;">Apply Now</a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="background: #f1f5f8;text-align: center;padding: 20px;border-radius: 10px;">
                            <table style="width: 100%;">
                                <tr>
                                    <td>
                                        <p style="color: #032946;font-size: 15px;margin: 0;font-weight:600;text-align: left;">
                                            All Rights Reserved | © {{date('Y')}} {{env('APP_NAME')}}
                                        </p>
                                    </td>
                                    <td>
                                        <ul style="list-style:none;margin:0">
                                            <li style="width:50%;float:left">
                                                <a href="#">
                                                    <img style="width:50px;" src="{{asset('public/uploads/email-template-new/icon1.png')}}" alt="image">
                                                </a>
                                            </li>
                                            <li style="width:50%;float:left">
                                                <a href="#">
                                                    <img  style="width:50px;" src="{{asset('public/uploads/email-template-new/icon2.png')}}" alt="image">
                                                </a>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" >
                            <a href="#" class="apply-btn" style="background-color: #113775;color: white;padding: 4px 30px;text-decoration: none;border-radius: 8px;font-weight: 500;display: inline-block;font-size: 15px;line-height: 30px;margin-top:30px; margin-bottom:30px;">View All Jobs</a>
                        </td>
                    </tr>
                    {{-- <tr>
                        <td style="">
                            <p style="color: #000;font-size: 18px;font-weight: bold;margin-bottom: 15px;padding-top:5px;">
                                Rajan Benipuri has shared following candidate's CV with you.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="">
                            <div class="job-tuple layout-wrapper" style="position: relative;
                                background: #ffffff;
                                border-radius: 20px;
                                margin-bottom: 16px;
                                padding: 24px 24px 20px;
                                transition: box-shadow .2s
                                linear; border: 1px solid#d4dbe3;">
                                <div class="row1" style="padding-bottom:5px;">
                                <h2 style="margin:0;"><a class="title " title="Web Designer" href="#" target="_blank" rel="noopener noreferrer" style="
                                    line-height: 22px;
                                    color: #032946;
                                    max-width: 100%;
                                    display: block;
                                    font-size: 18px;
                                    font-weight: 700;
                                    text-decoration: none;
                                    ">Web Designer</a></h2>
                                </div>
                                <div class=" row2" style="">
                                    <span class="">
                                        <a class=" comp-name " href="#" target="_blank" style="font-weight: 600;font-size: 18px;line-height: 18px;color: #494f6d;text-decoration:none;margin-top: 30px; display: block;padding-bottom:10px;">Mediboost</a>
                                    </span>
                                </div>
                                <div class=" row2" style="padding-bottom:5px;">
                                    <span class=" ">
                                        <a class="comp-name" href="#" target="_blank" style="font-weight: 500;font-size: 14px;line-height: 18px;color: #494f6d;text-decoration:none;margin:0;">Mediboost</a>
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" >
                            <a href="#" class="apply-btn" style="color: #113775;text-decoration: none;border-radius: 8px;font-weight: 700;display: inline-block;font-size: 17px;line-height: 30px;margin-bottom: 25px;margin-top: 10px;">View All Jobs</a>
                        </td>
                    </tr> --}}
                    <tr>
                        <td align="center" style="background: #f1f5f8;padding:40px 0px 0px "  >
                            {{-- <p style="color: #032946;font-size: 18px;font-weight: 500;margin-bottom: 15px;padding:0px;margin-top:0;">Rajan Benipuri has shared following candidate's CV with you.</p> --}}
                            <a href="#" target="_blank" class="store_icon me-2" style="display: inline-block;padding-right:8px">
                                <img src="{{asset('public/uploads/email-template-new/playstore.png')}}">
                            </a>
                            <a href="#" target="_blank" class="store_icon" style="display: inline-block;">
                                <img src="{{asset('public/uploads/email-template-new/applestore.png')}}">
                            </a>
                            <p style="border-bottom: 1px solid #d4dbe3;"></p>
                            <p style="font-weight: 500;font-size: 14px;line-height: 20px;color: #494f6d;padding: 10px 0;">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec odio. Quisque volutpat mattis eros. Nullam malesuada erat ut turpis. Suspendisse urna nibh viverra non semper suscipit posuere a pede.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div style="background-color: #2e64fe;padding: 15px 0;">
            <table style="width: 100%;border-collapse: collapse;">
                <tbody>
                    <tr>
                        <td style="padding:0 30px;padding-bottom: 2px;" valign="top">
                            <h3 style="margin: 0;color: #fff;margin-bottom: 15px;font-size: 20px;">{{env('APP_NAME')}}</h3>
                        </td>
                        <td align="right" valign="top" style="padding: 0 30px;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 30px;padding-bottom: 2px;" valign="top">
                            <p style="margin: 0;color: #fff;font-size: 15px;">{{env('APP_NAME')}} Simplified.</p>
                        </td>
                        <td align="right" valign="top" style="padding: 0 30px;">
                            <div class="footer-contact-list">
                                <a href="javascript:void(0)">
                                    <img src="{{asset('public/uploads/email-template-new/footer_logo.png')}}" style="height: 20px;">
                                </a>
                                <a href="javascript:void(0)">
                                    <img src="{{asset('public/uploads/email-template-new/instagram-icon.png')}}" style="height: 20px;margin: 0 3px;">
                                </a>
                                <a href="javascript:void(0)">
                                    <img src="{{asset('public/uploads/email-template-new/linkedin.png')}}" style="height: 20px;border-radius: 3px;">
                                </a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
   </body>
</html>

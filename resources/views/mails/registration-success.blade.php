<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <title>Registration Success</title>
      <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:ital,wght@0,100..900;1,100..900&display=swap"
         rel="stylesheet">
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
         tbody tr td{
         padding:0 20px;
         }
      </style>
   </head>
   <body>
      <div style="padding: 0px;border:1px solid#ededed;border-radius:10px;overflow: hidden;">
         <table style="width: 100%;border-collapse: collapse;">
            <thead>
               <tr style="background: #041a58;">
                  <td style="padding: 20px;">
                     <div class="logo" style="
                        width: 50%;
                        float: left;
                        ">
                        <img class="logo-img" src="{{ asset('public/uploads/hiringjet-white-logo.png') }}" alt="logo" style="width: 180px;object-fit: contain;">
                     </div>
                     <div class="text" style="
                        width: 50%;
                        float: right;
                        text-align: end;
                        color: #fff;
                        ">
                        <p style="text-align: right"><?= date('M d, Y') ?></p>
                     </div>
                  </td>
               </tr>
            </thead>
            <tbody>
               <tr>
                  <td
                     style="">
                     <p style="color: #000;font-size: 18px;font-weight: bold;margin-bottom: 5px;padding-top:10px;">Registration Completion With {{ env('APP_NAME') }}
                     </p>
                  </td>
               </tr>
               <tr>
                  <td
                     style="">
                     <p style="color: #4a4a4aba;font-size: 16px;font-weight: 400;margin-top: 5px;">Hello, {{ $full_name }}.
                     </p>
                  </td>
               </tr>
               <tr>
                  <td>
                     <div class="job-banner" style="width: 400px;border-radius: 15px;padding: 30px 20px;text-align: center;margin: 0 auto;border: 1px solid #ededed;margin-bottom: 50px;margin-top:20px;">
                        {{-- <div class="rounded" style="height: 100px;width: 100px;border-radius: 50px;background: #f1f5f8;margin: 0 auto;"></div> --}}
                        <h3 style="margin: 20px 0 0;font-size: 16px;color: #0d2531;letter-spacing: 1px;">{{ $content }}
                        </h3>
                        <h4 style="margin: 20px 0 0;font-size: 16px;color: #0d2531;letter-spacing: 1px;">Registered Username: {{ $email }}
                        </h4>
                        <h4 style="margin: 20px 0 0;font-size: 16px;color: #0d2531;letter-spacing: 1px;">Login Password: {{ !empty($pwd) ? $pwd : 'Password enter in registration step 1.'}}
                        </h4>
                     </div>
                  </td>
               </tr>
            </tbody>
            <tfoot>
               <tr>
                  <td style="background: #f1f5f8;text-align: center;padding: 40px 0;">
                     <p style="color: #7d8286;font-size: 15px;font-weight: 500;margin: 0;">All Rights Reserved | © <?= date('Y')?> {{ env('APP_NAME') }}
                     </p>
                  </td>
               </tr>
            </tfoot>
         </table>
      </div>
   </body>
</html>

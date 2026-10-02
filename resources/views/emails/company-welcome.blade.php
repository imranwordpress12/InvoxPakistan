<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, Helvetica, sans-serif; color:#333333;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f6f8; padding:40px 15px;">
        <tr>
            <td align="center">

                <!-- Email Container -->
                <table width="600" cellpadding="0" cellspacing="0" border="0"
                       style="max-width:600px; width:100%; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06);">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding:30px 30px 25px; border-bottom:1px solid #eeeeee;">

                            <!-- Replace with your logo -->
                            <img src="https://invox.pk/images/logo.jpeg" alt="{{ config('app.name') }} Logo"
                                 style="max-width:120px; height:auto; display:block; margin-bottom:10px;">
                            <div style="font-size:28px; font-weight:bold; color:#1f2937;">
                                {{ config('app.name') }}
                            </div>

                            <div style="font-size:14px; color:#6b7280; margin-top:8px;">
                                Welcome to our platform
                            </div>

                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:40px 40px 30px;">

                            <h1 style="margin:0 0 20px; font-size:26px; line-height:1.3; color:#111827;">
                                Welcome, {{ $company->name }}!
                            </h1>

                            <p style="margin:0 0 18px; font-size:16px; line-height:1.7; color:#4b5563;">
                                Thank you for joining <strong>{{ config('app.name') }}</strong>.
                                Your company account has been created successfully.
                            </p>

                            <p style="margin:0 0 25px; font-size:16px; line-height:1.7; color:#4b5563;">
                                You can now sign in using your company credentials and start
                                managing your account.
                            </p>

                            <!-- CTA Button -->
                            <table cellpadding="0" cellspacing="0" border="0" style="margin:30px 0;">
                                <tr>
                                    <td align="center" style="border-radius:6px; background-color:#2563eb;">
                                        <a href="https://invox.pk/company/login" target="_blank"
                                           style="display:inline-block; padding:14px 28px; font-size:15px; font-weight:bold; color:#ffffff; text-decoration:none; border-radius:6px;">
                                            Sign In to Your Account
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:25px 0 0; font-size:14px; line-height:1.6; color:#6b7280;">
                                If you did not request this account, you can safely ignore this
                                email or contact our support team.
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:25px 30px; background:#f9fafb; border-top:1px solid #eeeeee; text-align:center;">

                            <p style="margin:0 0 8px; font-size:13px; color:#6b7280;">
                                Regards,<br>
                                <strong style="color:#374151;">
                                    {{ config('app.name') }}
                                </strong>
                            </p>

                            <p style="margin:12px 0 0; font-size:12px; color:#9ca3af;">
                                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
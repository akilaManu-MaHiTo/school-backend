<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Welcome to {{ $organizationFactoryName }} Factory!</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f7f7f7; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <div style="text-align: center; background-color: #6a8ff3; padding: 20px;">
            <img src="{{ $logoUrl ?? asset('/assets/images/logo-blue.png') }}" alt="{{ $organizationName }} Logo" style="width: 100px; height: auto;">

        </div>
        <div style="padding: 30px;">
            <h1 style="font-size: 22px; color: #333; margin-bottom: 20px; border-bottom: 1px solid #e0e0e0; padding-bottom: 10px; text-align: center;">
                Welcome to {{ $organizationFactoryName }} Factory!
            </h1>
            <p style="font-size: 16px; color: #333;">Hello <strong>{{ $name }}</strong>,</p>
            <p style="font-size: 16px; color: #333; margin-top: 30px;">We are delighted to welcome you to {{ $organizationFactoryName }} Factory, our sustainability initiative for garment factory employees. By joining, you are taking a step towards a greener future and a healthier workplace.</p>
            <p style="font-size: 16px; color: #333; margin-top: 20px;">Our platform helps you track your sustainable practices, access educational resources, and contribute to an eco-friendly work environment.</p>
            <p style="font-size: 16px; color: #333; margin-top: 20px;">If you have any questions or need assistance, our support team is always ready to help. Let's make sustainability a shared journey!</p>
            <p style="font-size: 16px; color: #333; margin-top: 30px;">Thank you for being a part of the change!</p>
            <p style="font-size: 16px; color: #333; margin-top: 30px;">Best regards,</p>
            <p style="font-size: 16px; color: #333; font-weight: bold; margin: 5px 0;">The {{ $organizationFactoryName }} Factory Team</p>
        </div>
        <div style="text-align: center; padding: 10px; background-color: #3b82f6;">
            <p style="font-size: 12px; color: #ffffff;">&copy; {{ date('Y') }} chasseur-dynamics Group. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

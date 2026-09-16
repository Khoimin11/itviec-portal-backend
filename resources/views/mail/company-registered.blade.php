<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo tạo tài khoản thành công</title>
</head>
<body style="margin:0;padding:24px 12px;background-color:#111111;font-family:Arial,sans-serif;font-size:16px;line-height:1.7;color:#f5f5f5;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:768px;background-color:#292929;border-radius:20px;">
                    <tr>
                        <td style="padding:20px;">
                            <p style="margin:0;padding-bottom:12px;border-bottom:1px solid #383838;">Thông báo tạo tài khoản thành công</p>
                            <p>Kính gửi Anh/Chị,</p>
                            <p>ITViec xin thông báo tài khoản của Anh/Chị đã được tạo thành công trên hệ thống.</p>
                            <p>Thông tin đăng nhập:</p>
                            <ul style="padding-left:28px;">
                                <li>Tài khoản: <strong>{{ $email }}</strong></li>
                                <li>Mật khẩu được cấp: <code style="font-size:16px;white-space:pre-wrap;word-break:break-all;">{{ $password }}</code></li>
                            </ul>
                            <p>Anh/Chị vui lòng sử dụng thông tin trên để đăng nhập vào hệ thống.</p>
                            <p style="margin-bottom:0;">Để đảm bảo an toàn và bảo mật thông tin, Anh/Chị nên thay đổi mật khẩu sau lần đăng nhập đầu tiên và không chia sẻ mật khẩu với người khác.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

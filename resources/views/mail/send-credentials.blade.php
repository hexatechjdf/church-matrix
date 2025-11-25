<!DOCTYPE html>
<html>
<head>
    <title>Fence System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">

</head>
<body>
    <div class="row">
        <div class="col-lg-12">
            <table class="body-wrap" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; background-color: transparent; margin: 0;" bgcolor="transparent">
                <tr>
                    <td class="container" width="800" style=" display: block !important; max-width: 800px !important; clear: both !important; margin-left:100px; " valign="top">
                        <div class="content" style="padding: 20px;">
                            <table class="main" width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid rgba(130, 134, 156, 0.15);" bgcolor="transparent">

                                <tr>
                                    <td class="alert alert-primary border-0 bg-primary" style="padding: 20px; border-radius: 0;" align="center" valign="top">
                                        <h1> Fence System </h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="alert alert-dark border-0" style="color:#ffffff; background-color: #212f56; padding: 20px; border-radius: 0;" align="center" valign="top">
                                        Important : Login Credential for Fence System Created / Updated
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-wrap" style="padding: 20px;" valign="top">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; font-size: 14px; padding: 10px" valign="top">
                                                     <strong style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;"> {{ $credetials['title'] }} </strong> .
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; font-size: 14px; padding: 10px 10px 20px;" valign="top">
                                                    {{ $credetials['body'] }}
                                                </td>
                                            </tr>
                                            <div style="margin:20px;">
                                                <span style="font-weight: bold; font-size:17px;font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; margin-top:20px;">Email : <span>{{ $credetials['email'] }}</span></span><br><br>
                                                <span style="font-weight: bold; font-size:17px;font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; margin-top:8px;">Password : <span>{{ $credetials['password'] }}</span></span>
                                            </div>
                                            <tr>
                                                <td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; font-size: 14px; padding: 0 0 20px;" valign="top">
                                                    <a href="{{ route('login') }}" class="btn-gradient-primary" style=" font-size: 14px; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: block; border-radius: 5px; text-transform: capitalize; border: none; padding: 10px 20px; margin-top:30px;">Login to Fence System</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="content-block" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; font-size: 14px; text-align: center;" valign="top">
                                                    Thanks for choosing <b>Fence System</b>.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0;" valign="top"></td>
                </tr>
            </table><!--end table-->
        </div><!--end col-->
    </div><!--end row-->

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF" crossorigin="anonymous"></script>

</body>
</html>

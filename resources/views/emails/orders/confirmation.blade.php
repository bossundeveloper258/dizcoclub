<!DOCTYPE html>
<html>
<head>
 <title>Laravel 8 Send Email Example</title>
</head>
<body>
 
    <table cellspacing="0" cellpadding="0" style="width: 100%;">
        <STYLE>
        <!--
        a:black:hover {
            color : #959595 ;
            text-decoration : none;
        }
        a.black:link, a:visited {
            color : #959595 ;
            text-decoration : none;
        }
        a.black:hover {
            color : #000000;
            text-decoration : none;
        }
        a:gold:hover {
            color : #ffffff;
            text-decoration : none;
        }
        a.gold:link, a:visited {
            color : #ffffff;
            text-decoration : none;
        }
        a.gold:hover {
            color : #000000;
            text-decoration : none;
        }
        -->
        <!--
        @import url(http://fonts.googleapis.com/css?family=Raleway:800,500);
        -->
        </STYLE>
        
        <tr>
            <td bgcolor="#e94985">  
                @foreach ($clients as $client)
                    <p style="font-size:18px;color:#ffffff;font-family:'Raleway', Verdana, Arial, Helvetica, sans-serif;margin-bottom: 15px;margin-top:8px; line-height: 1.5; font-weight:600;text-align: center;">TICKET {{ $client->ticket }}</p>
                    <img src="{{ $client->qr }}" alt="" style="display: inherit;margin: 0 auto;width: 250px;margin-bottom: 15px;">
                    <p style="font-size:14px;color:#ffffff;font-family:'Raleway', Verdana, Arial, Helvetica, sans-serif;margin-top:8px; line-height: 1.5; font-weight:600;text-align: center;">NOMBRES</p>
                    <p style="font-size:14px;color:#ffffff;font-family:'Raleway', Verdana, Arial, Helvetica, sans-serif;margin-top:8px; line-height: 1.5; font-weight:300;text-align: center;margin-bottom: 20px;">{{$client->name}}</p>
                    <p style="font-size:14px;color:#ffffff;font-family:'Raleway', Verdana, Arial, Helvetica, sans-serif;margin-top:8px; line-height: 1.5; font-weight:600;text-align: center;">DNI</p>
                    <p style="font-size:14px;color:#ffffff;font-family:'Raleway', Verdana, Arial, Helvetica, sans-serif;margin-top:8px; line-height: 1.5; font-weight:300;text-align: center;margin-bottom: 20px;">{{$client->dni}}</p>
                @endforeach
                
            </td>
        </tr>
        <tr>
            <td bgcolor="#e94985">  
                <hr style="width: 100%;">
            </td>
        </tr>  
        <tr>
            <td bgcolor="#e94985">
                <div style="text-align: center; width: 100%;margin-top: 20px;">
                    <img src="{{ $image }}" alt="" width="150px">
                    
                </div>
                <div style="text-align: center; width: 100%;margin-top: 20px;">
                    <p style="font-size: 18px;font-weight: 600;font-family:Arial, Helvetica, sans-serif;color: #FFFFFF; margin-bottom:0; margin-top:15px;text-align: center;">
                        {{ $title }}
                    </p>
                    <p style="font-size: 18px;font-weight: 600;font-family:Arial, Helvetica, sans-serif;color: #FFFFFF; margin-bottom:0; margin-top:15px;text-align: center;">
                        {{ $date }}
                    </p>
                    <p style="font-size: 18px;font-weight: 600;font-family:Arial, Helvetica, sans-serif;color: #FFFFFF; margin-bottom:0; margin-top:15px;text-align: center;">
                        Ingreso antes de las {{ $hour }}
                    </p>
                </div>
                <div style="text-align: center; width: 100%;padding-top: 3rem;">
                </div>
            </td>
        </tr>
    </table>
</body>
</html> 


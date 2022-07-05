<table cellspacing="0" cellpadding="0">
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
        <td bgcolor="#e94985">  
            @foreach ($clients as $client)
                <p tyle="font-size:13px;color:#ffffff;font-family:'Raleway', Verdana, Arial, Helvetica, sans-serif;margin-bottom:0px;margin-top:5px; line-height: 1.5; font-weight:500;">TICKET {{ $client->ticket }}</p>
                <img src="{{ $client->qr }}" alt="">
            @endforeach
            
        </td>
    </tr>
    <tr>
        <td bgcolor="#e94985"></td>
        <td bgcolor="#e94985">
            <div style="text-align: center; width: 100%">
                <img src="{{ $image }}" alt="" width="150px">
            </div>
            
            <p style="font-size: 11px;font-family:Arial, Helvetica, sans-serif;color: #a9a9a9; margin-bottom:0; margin-top:15px;">
                {{ $title }}
            </p>
            
        </td>
    </tr>
</table>
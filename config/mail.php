<?php
return ['default'=>env('MAIL_MAILER','log'),'mailers'=>[
'smtp'=>['transport'=>'smtp','scheme'=>env('MAIL_SCHEME','smtp'),'host'=>env('MAIL_HOST','127.0.0.1'),'port'=>(int)env('MAIL_PORT',587),'username'=>env('MAIL_USERNAME'),'password'=>env('MAIL_PASSWORD'),'timeout'=>15,'local_domain'=>parse_url(env('APP_URL','http://localhost'),PHP_URL_HOST)],
'log'=>['transport'=>'log','channel'=>'single'],'array'=>['transport'=>'array']],
'from'=>['address'=>env('MAIL_FROM_ADDRESS','pos@example.com'),'name'=>env('MAIL_FROM_NAME','Counterpoint POS')]];

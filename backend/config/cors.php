<?php
return [
 'enable'=>true, 'allow_origins'=>['http://localhost:5689','http://127.0.0.1:5689'],
 'allow_methods'=>['GET','POST','PUT','PATCH','DELETE','OPTIONS'], 'allow_headers'=>['*'],
 'expose_headers'=>[], 'allow_credentials'=>true, 'max_age'=>86400,
];

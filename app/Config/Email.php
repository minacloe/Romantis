<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $SMTPHost = 'smtp.gmail.com';  
    public string $SMTPUser = '';  // Email pengirim
    public string $SMTPPass = '';  // Password aplikasi Gmail
    public int $SMTPPort = 587; 
    public string $SMTPCrypto = 'tls';  

    public string $fromEmail  = 'romantisprakomstatistisi@gmail.com';
    public string $fromName   = '';

    public string $userAgent = 'CodeIgniter';
    public string $protocol = 'smtp';  
    public string $mailPath = '/usr/sbin/sendmail';
    public int $SMTPTimeout = 5;
    public bool $SMTPKeepAlive = false;
    public bool $wordWrap = true;
    public int $wrapChars = 76;
    public string $mailType = 'html'; 
    public string $charset = 'UTF-8';
    public bool $validate = true;
    public int $priority = 3;
    public string $CRLF = "\r\n";
    public string $newline = "\r\n";
    public bool $BCCBatchMode = false;
    public int $BCCBatchSize = 200;
    public bool $DSN = false;
}

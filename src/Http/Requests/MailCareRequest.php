<?php

namespace BeyondCode\Mailbox\Http\Requests;

use BeyondCode\Mailbox\InboundEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class MailCareRequest extends FormRequest
{
    public function validator()
    {
        return Validator::make([], []);
    }

    public function email()
    {
        return InboundEmail::fromMessage($this->getRawRequest());
    }

    protected function getRawRequest(): string
    {
        $rawBodyFromApache = file_get_contents('php://input');
        $headersFromApache = getallheaders();
        if($rawBodyFromApache && $headersFromApache !== false && count($headersFromApache)> 0){
            $rawHeader = "";
            foreach($headersFromApache as $key => $value){
                $rawHeader .= "{$key}: {$value}\r\n";
            }
            return "{$rawHeader}\r\n{$rawBodyFromApache}";
        }
        return $this->__toString();
    }
}

<?php declare(strict_types=1);

namespace App\Command\ContactForm;

use App\Command\CommandInterface;
use App\Validator\Captcha\Captcha;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Sequentially;

class ContactFormCommand implements CommandInterface
{
    #[Sequentially(constraints: [
        new NotBlank(groups: ['send']),
        new Email(groups: ['send']),
    ])]
    public $email;

    #[Sequentially(constraints: [
        new NotBlank(groups: ['send']),
        new Length(max: 10000, groups: ['send']),
    ])]
    public $message;

    #[Captcha(groups: ['send'])]
    public $captcha;
}

<?php
// src/Service/EventValidatorService.php
namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use App\Entity\Event;
use DateTime;

class EventValidatorService
{

  private $dateFormat = "d/m/Y";

  public function __construct(private ValidatorInterface $validator) {}

  public function validate(Event $event): array
  {
    $errors = $this->validator->validate($event);
    $errorMessages = [];
    foreach ($errors as $error) {
      $errorMessages[$error->getPropertyPath()] = $error->getMessage();
    }

    return $errorMessages;
  }

  public function isValidDateTime(string $dateTime): DateTime|false
  {
    if (!$dateTime) return false;
    return DateTime::createFromFormat($this->dateFormat, $dateTime);
  }
}

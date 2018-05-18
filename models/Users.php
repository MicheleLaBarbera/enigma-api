<?php

namespace Model;

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;

class Users extends Model {
  public function validation() {
    $validator = new Validation();

    // User name must be unique
    $validator->add(
      'username',
      new UniquenessValidator([
        'field'   =>  $this,
        'message' => "L'username inserito è già stato registrato.",
      ])
    );

    return $this->validate($validator);
  }
}

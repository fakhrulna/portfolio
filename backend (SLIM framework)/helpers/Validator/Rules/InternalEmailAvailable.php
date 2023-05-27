<?php

namespace helpers\Validator\Rules;

use models\Internal\InternalUserModel as InternalUser;
use Respect\Validation\Rules\AbstractRule;

class InternalEmailAvailable extends AbstractRule
{

    /**
     * Validating email available for new user
     * @param $input
     * @return bool
     */
    public function validate($input)
    {
        return InternalUser::where('email', $input)->count() === 0;
    }
}
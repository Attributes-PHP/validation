<?php

declare(strict_types=1);

namespace Attributes\Validation\Validators;

use Attributes\Validation\Context;
use Attributes\Validation\ErrorInfo;
use Attributes\Validation\Exceptions\ContinueValidationException;
use Attributes\Validation\Exceptions\ValidationException;
use Attributes\Validation\Property;
use Respect\Validation\Exceptions\ValidationException as RespectValidationException;

class ChainValidator implements PropertyValidator
{
    private array $allValidators = [];

    /**
     * Yields all validation rules from multiple rules extractors
     *
     * @param  Property  $property  - Property to yield the rules from
     *
     * @throws ValidationException
     * @throws ContinueValidationException - When errors do exist, and we still want to check the remaining data
     */
    public function validate(Property $property, Context $context): void
    {
        $errorInfo = $context->get(ErrorInfo::class);
        foreach ($this->allValidators as $validator) {
            try {
                $validator->validate($property, $context);
            } catch (ValidationException|RespectValidationException $error) {
                $errorInfo->addError($error);
            } catch (ContinueValidationException $error) {
            }
        }

        if ($errorInfo->hasErrors()) {
            throw new ContinueValidationException('Continue validation');
        }
    }

    public function add(PropertyValidator $validator): void
    {
        $this->allValidators[] = $validator;
    }
}

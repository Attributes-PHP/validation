<?php

/**
 * Holds a base class for validating data via the Symfony validator
 *
 * @since 1.2.0
 *
 * @license MIT
 */

declare(strict_types=1);

namespace Attributes\Validation;

use Attributes\Validation\Config\Config;
use Attributes\Validation\Config\Options\From;

/**
 * Contract for creating request validation models.
 *
 *      use Respect\Validation\Rules as Rules;
 *      use Attributes\Validation\BaseModel;
 *
 *       class Person extends BaseModel {
 *          #[Rules\NumericVal]
 *          private float|int $age;
 *       }
 *
 * @since 1.2.0
 *
 * @author André Gil <andre_gil22@hotmail.com>
 */
abstract class BaseModel
{
    /**
     * Holds model validation configurations
     *
     * @internal
     */
    private Config $_config;

    public function __construct(From $from = From::JSON, ...$additionalOptions)
    {
        $this->_config = new Config($from, ...$additionalOptions);
    }

    /**
     * Retrieves the validation configs to be applied during the validation
     */
    public function getConfig(): Config
    {
        return $this->_config;
    }
}

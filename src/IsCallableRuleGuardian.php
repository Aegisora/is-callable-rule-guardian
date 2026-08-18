<?php

namespace Aegisora\RuleGuardians\IsCallableRule;

use Aegisora\Guardian\Exceptions\GuardianExecutingRuleException;
use Aegisora\Guardian\Exceptions\GuardianValidationException;
use Aegisora\Guardian\Guardian;
use Aegisora\Rules\IsCallableRule;
use Throwable;

class IsCallableRuleGuardian
{
    private Guardian $guardian;

    public function __construct(
        Guardian $guardian
    ) {
        $this->guardian = $guardian;
    }

    /**
     * @param mixed $value
     * @throws GuardianExecutingRuleException
     * @throws GuardianValidationException
     * @throws Throwable
     */
    public function check(
        $value,
        ?Throwable $exception = null
    ): void {
        $this->guardian->check($value, IsCallableRule::create(), $exception);
    }
}

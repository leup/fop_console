<?php

declare(strict_types=1);

namespace FOP\Console;

class FOPCommandFormatsValidator
{
    /**
     * @var array Validation messages
     */
    private $validationMessages = [];

    /**
     * @param string $commandDomain domain, e.g. Module
     * @param string $commandClassName php class name, e.g. ModuleHooks
     * @param string $commandName symfony command name, e.g. fop:modules:hooks
     * @param string $commandServiceName service name defined in config/services.yml. e.g. fop.console.modules.module_hooks.command
     *
     * @return bool
     */
    public function validate(
        string $commandDomain,
        string $commandClassName,
        string $commandName,
        string $commandServiceName
    ): bool {
        if (empty($commandDomain)) {
            $this->addValidationMessage(
                $commandClassName,
                "Domain can't be empty."
            );

            return false;
        }

        if (strpos($commandClassName, $commandDomain) !== 0) {
            $this->addValidationMessage(
                $commandClassName,
                "Domain $commandDomain must be included in command class name."
            );

            return false;
        }

        $action = str_replace($commandDomain, '', $commandClassName);

        if (empty($action)) {
            $this->addValidationMessage(
                $commandClassName,
                "Action can't be empty."
            );

            return false;
        }

        ucfirst($commandDomain);
        ucfirst($action);

        $expectedCommandNamePattern = strtolower('fop:' . implode('-', $this->getWords($commandDomain)) . ':'
            . implode('[:-]', $this->getWords($action)));
        if (!preg_match('/^' . $expectedCommandNamePattern . '$/', $commandName)) {
            $this->addValidationMessage(
                $commandClassName,
                'Wrong format for command class name.' . PHP_EOL
                    . "Expected = $expectedCommandNamePattern" . PHP_EOL
                    . "Actual = $commandName"
            );

            return false;
        }

        $expectedCommandServiceNamePattern = strtolower('fop.console.' . implode('_', $this->getWords($commandDomain)) . '.'
            . implode('[\._]', $this->getWords($action)) . '.command');
        if (!preg_match('/^' . $expectedCommandServiceNamePattern . '$/', $commandServiceName)) {
            $this->addValidationMessage(
                $commandClassName,
                'Wrong format for command service name.' . PHP_EOL
                    . "Expected = $expectedCommandServiceNamePattern" . PHP_EOL
                    . "Actual = $commandServiceName"
            );

            return false;
        }

        return true;
    }

    private function getWords($subject)
    {
        return preg_split('/(?=[A-Z])/', $subject, -1, PREG_SPLIT_NO_EMPTY);
    }

    private function addValidationMessage(string $command, string $message)
    {
        $this->validationMessages[] = "[$command] => " . $message;
    }

    public function getValidationMessages()
    {
        return $this->validationMessages;
    }
}

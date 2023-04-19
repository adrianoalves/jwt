<?php

namespace AdrianoAlves\Jwt\Commands\Concerns;

trait OverwritesEnv
{
    /**
     * Read the "key=value" string of a given key from the env file.
     * This function returns original "key=value" string and doesn't modify it.
     *
     * @param string $envFileContent
     * @param string $key
     *
     * @return string|null Key=value string or null if the key is not exists.
     */
    public function readKeyValuePair(string $envFileContent, string $key): ?string
    {
        // Match the given key at the beginning of a line
        if (preg_match("#^ *{$key} *= *[^\r\n]*$#uimU", $envFileContent, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Overwrite the contents of env file
     *
     * @param string $contents
     *
     * @return boolean
     */
    protected function writeFile(string $contents): bool
    {
        return (bool)file_put_contents($this->environmentFilePath, $contents, \LOCK_EX);
    }

    /**
     * Execute the console command.
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function writeEnvFile(string $key, string $value): bool
    {
        $newEnvFileContent = $this->setEnvVariable($key, $value);
        return $this->writeFile($newEnvFileContent);
    }

    /**
     * Set or update env-variable.
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    public function setEnvVariable(string $key, string $value): string
    {
        $envFileContent = file_get_contents($this->environmentFilePath);

        $oldPair = $this->readKeyValuePair($envFileContent, $key);

        // Wrap values that have a space or equals in quotes to escape them
        if (preg_match('/\s/',$value) || str_contains($value, '=')) {
            $value = '"' . $value . '"';
        }

        $newPair = $key . '=' . $value;

        // when key exists.
        if ($oldPair !== null) {
            return preg_replace('/^' . preg_quote($oldPair, '/') . '$/uimU', $newPair, $envFileContent);
        }

        // New key.
        return $envFileContent . "\n" . $newPair . "\n";
    }
}
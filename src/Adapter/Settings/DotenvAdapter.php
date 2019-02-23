<?php

namespace Kilmas\GxcRpc\Adapter\Settings;

use Kilmas\GxcRpc\Exception\SettingsException;
use Kilmas\GxcRpc\Exception\SettingsNotFoundException;
use Dotenv\Dotenv;

/**
 * Class DotenvAdapter
 *
 * The dotenv adaptor for loading settings
 */
class DotenvAdapter implements SettingsInterface
{
    /**
     * DotenvAdapter constructor
     *
     * @param Dotenv $client
     */
    public function __construct(Dotenv $settings)
    {
        try {
            $settings->load();
        } catch (\Dotenv\Exception\InvalidPathException $e) {
            throw new SettingsNotFoundException('Invalid path to settings config file');
        } catch (\Throwable $t) {
            throw new SettingsException('Access to settings failed');
        }
    }

    /**
     * @inheritdoc
     */
    public function rpcNode(): string
    {
        return (string) getenv('RPC_NODE_URL');
    }
}

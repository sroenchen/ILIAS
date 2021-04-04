<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Service context for scorm
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 */
class ilContextScorm implements ilContextTemplate
{
    /**
     * Are redirects supported?
     *
     * @return bool
     */
    public static function supportsRedirects()
    {
        return false;
    }
    
    /**
     * Based on user authentication?
     *
     * @return bool
     */
    public static function hasUser()
    {
        return true;
    }
    
    /**
     * Uses HTTP aka browser
     *
     * @return bool
     */
    public static function usesHTTP()
    {
        return false;
    }
    
    /**
     * Has HTML output
     *
     * @return bool
     */
    public static function hasHTML()
    {
        return false;
    }
    
    /**
     * Uses template engine
     *
     * @return bool
     */
    public static function usesTemplate()
    {
        return false;
    }
    
    /**
     * Init client
     *
     * @return bool
     */
    public static function initClient()
    {
        return true;
    }
    
    /**
     * Try authentication
     *
     * @return bool
     */
    public static function doAuthentication()
    {
        return false;
    }

    /**
     * Check if persistent session handling is supported
     * @return boolean
     */
    public static function supportsPersistentSessions()
    {
        return false;
    }
    
    /**
     * Supports push messages
     *
     * @return bool
     */
    public static function supportsPushMessages()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function isSessionMainContext()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public static function modifyHttpPath(string $httpPath) : string
    {
        return $httpPath;
    }
}

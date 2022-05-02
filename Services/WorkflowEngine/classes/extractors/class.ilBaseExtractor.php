<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBaseExtractor
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
abstract class ilBaseExtractor implements ilExtractor
{
    /** @var ilExtractedParams $ilExtractedParams */
    protected ilExtractedParams $ilExtractedParams;

    /**
     * ilBaseExtractor constructor.
     *
     * @param \ilExtractedParams $ilExtractedParams
     */
    public function __construct(ilExtractedParams $ilExtractedParams)
    {
        $this->ilExtractedParams = $ilExtractedParams;
    }

    /**
     * @param string $event
     * @param array  $parameters
     */
    abstract public function extract(string $event, array $parameters) : ilExtractedParams;

    /**
     * @param array $parameters
     */
    protected function extractWithUser(array $parameters) : void
    {
        $this->ilExtractedParams->setSubjectId($parameters['obj_id']);
        $this->ilExtractedParams->setContextType('usr_id');
        $this->ilExtractedParams->setContextId($parameters['usr_id']);
    }

    /**
     * @param array $parameters
     */
    protected function extractWithoutUser(array $parameters) : void
    {
        $this->ilExtractedParams->setSubjectId($parameters['obj_id']);
        $this->ilExtractedParams->setContextType('null');
        $this->ilExtractedParams->setContextId(0);
    }
}

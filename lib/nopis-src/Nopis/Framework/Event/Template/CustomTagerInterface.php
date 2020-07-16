<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nopis\Framework\Event\Template;

/**
 *
 * @author wb
 */
interface CustomTagerInterface
{
    /**
     * Inject tag defined by user.
     *
     * @param \Nopis\Framework\Event\Template\TemplateEngineInvokeEvent $event
     */
    public function addTags(TemplateEngineInvokeEvent $event);
}

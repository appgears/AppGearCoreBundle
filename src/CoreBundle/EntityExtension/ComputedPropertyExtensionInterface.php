<?php

namespace AppGear\CoreBundle\EntityExtension;

interface ComputedPropertyExtensionInterface
{
    /**
     * @param object $object  The object for processing
     * @param string $field   Extension attached to the field
     * @param array  $options The extension options
     *
     * @return mixed
     */
    public function execute($object, $field, array $options = []);
}
<?php
namespace Regiosoft\StripeBundle;

use Regiosoft\StripeBundle\DependencyInjection\RegiosoftStripeExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RegiosoftStripeBundle extends Bundle
{
    /**
     * {@inheritDoc}
     * @version 0.0.1
     * @since 0.0.1
     */
    public function getContainerExtension()
    {
        // this allows us to have custom extension alias
        // default convention would put a lot of underscores
        if (null === $this->extension) {
            $this->extension = new RegiosoftStripeExtension();
        }

        return $this->extension;
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        // TODO: Implement getParent() method.
    }
}
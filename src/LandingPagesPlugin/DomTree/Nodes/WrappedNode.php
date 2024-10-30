<?php
namespace LandingPages\Wordpress\Plugin\LandingPagesPlugin\DomTree\Nodes;

interface WrappedNode
{
    /**
     * @return \DOMNode
     */
    public function getDomNode();
}

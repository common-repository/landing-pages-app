<?php
namespace LandingPages\Wordpress\Plugin\Framework\Event;

use LandingPages\Wordpress\Plugin\Framework\Kernel\PluginPartInterface;

class PostTemplateFilter extends AbstractEvent implements PluginPartInterface
{
    const FILTER_TAG = 'single_template';

    public function filter()
    {
        $object = get_queried_object();

        if (array_key_exists($object->post_type, $this->containerCollection->get('framework.post.type.collection')->getPostTypes())) {
            return $this->containerCollection->get('framework.kernel')->getConfig('landing_pages_singlepost_path');
        } else {
            return $this->filterArguments['singleTemplate'];
        }
    }

    public function initialize()
    {
        add_filter(self::FILTER_TAG, function ($singleTemplate) {
            $this->filterArguments['singleTemplate'] = $singleTemplate;
            return $this->filter();
        }, 99);
    }
}

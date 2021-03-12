<?php

namespace Bleumi\PaymentAggregator\Block\Adminhtml\System\Config\Fieldset;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Hint extends Template implements RendererInterface
{

    /**
     * Render method
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '';

        if ($element->getComment()) {
            $html .= sprintf('<tr id="row_%s">', $element->getHtmlId());
            $html .= '<td colspan="1"><p class="note"><span>';
            $html .= sprintf(
                '<div class="bleumi-warning-note"><p>Please configure your payment methods in the Bleumi Portal.</p><ul class="config_points"><li>Add your gateway accounts</li><li>Configure and enable payment methods</li><li>Create an API key</li></ul><p>Login to <a target="_blank" href="https://account.bleumi.com/account/?app=paymentlink&tab=gateway">Bleumi Portal</a> to start the setup process.</p><p>For more assitance please contact <a href="mailto:support@bleumi.com">support@bleumi.com</a></p></div>',
                $element->getComment()
            );
            $html .= '</span></p></td></tr>';
        }

        return $html;
    }
}

<?php
// coverage/generated-shapes.php — the generated-code world the detection
// phase lives in (`…Factory`, `\Proxy`, `…Interceptor`), copied in shape
// from a real 2.4 install's generated/ tree (dev/tests has none of these).
// Bodies are skipped; signatures, hierarchy, and the use of the Interception
// trait are what the fingerprint pins.

namespace Corp\Generated;

/**
 * Factory class for @see \Corp\Generated\Widget
 */
class WidgetFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Corp\\Generated\\Widget')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Corp\Generated\Widget
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}

/**
 * Interceptor class for @see \Corp\Generated\Widget
 */
class WidgetInterceptor extends \Corp\Generated\Widget implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->___init();
        parent::__construct($objectManager);
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Corp\Generated\Template $template, array $data = [])
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'render');
        return $pluginInfo ? $this->___callPlugins('render', func_get_args(), $pluginInfo) : parent::render($template, $data);
    }
}

/**
 * Proxy class for @see \Corp\Generated\Session
 */
class SessionProxy extends \Corp\Generated\Session implements \Magento\Framework\ObjectManager\NoninterceptableInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Proxied instance name
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Proxied instance
     *
     * @var \Corp\Generated\Session
     */
    protected $_subject = null;

    /**
     * Instance shareability flag
     *
     * @var bool
     */
    protected $_isShared = null;

    /**
     * Proxy constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param bool $shared
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Corp\\Generated\\Session', $shared = true)
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
        $this->_isShared = $shared;
    }

    /**
     * {@inheritdoc}
     */
    public function start(): void
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_subject = $this->_getSubject();
        return $this->_subject->start();
    }

    /**
     * Get proxied instance
     *
     * @return \Corp\Generated\Session
     */
    protected function _getSubject(): \Corp\Generated\Session
    {
        if (!$this->_subject) {
            $this->_subject = true === $this->_isShared
                ? $this->_objectManager->get($this->_instanceName)
                : $this->_objectManager->create($this->_instanceName);
        }
        return $this->_subject;
    }
}

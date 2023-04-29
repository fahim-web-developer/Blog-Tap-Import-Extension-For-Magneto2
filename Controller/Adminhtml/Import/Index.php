<?php
 
namespace Fahim\BlogTagImport\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Fahim_BlogTagImport::tagimport');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Fahim_BlogTagImport::tagimport')
            ->addBreadcrumb(__('Tag'), __('Tag'))
            ->addBreadcrumb(__('Import Tag'), __('Import Tag'));
        return $resultPage;
    }

    /**
     * Edit Brand Page
     */
    public function execute(){
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Import Tag'));
        return $resultPage;
    }
}
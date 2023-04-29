<?php
 
namespace Fahim\BlogTagImport\Controller\Adminhtml\Import;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product\Url;
use Mageplaza\Blog\Model\TagFactory;


class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $_configResource;

    /**
    * CSV Processor
    *
    * @var \Magento\Framework\File\Csv
    */
    protected $csvProcessor;

    /**
    * productFactory
    *
    * @var \Magento\Catalog\Model\ProductFactory
    */
    protected $productFactory;
    /**
     * @param \Magento\Backend\App\Action\Context                          $context           
     * @param \Magento\Framework\View\Result\PageFactory                   $resultPageFactory        
     * @param \Magento\Framework\Filesystem                                $filesystem        
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager      
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $scopeConfig       
     * @param \Magento\Framework\App\ResourceConnection                    $resource          
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configResource    
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configResource,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\File\Csv $csvProcessor,
        Url $url,
        TagFactory $tagFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_filesystem       = $filesystem;
        $this->_storeManager     = $storeManager;
        $this->_scopeConfig      = $scopeConfig;
        $this->_configResource   = $configResource;
        $this->_resource         = $resource;
        $this->mediaConfig       = $mediaConfig;
        $this->csvProcessor = $csvProcessor;
        $this->productFactory = $productFactory;
        $this->url_key = $url;
        $this->tagFactory = $tagFactory; 
    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {

    
        $data = $this->getRequest()->getParams();
        $filePath = $fileContent = '';
        try {
            $uploader = $this->_objectManager->create(
                'Magento\MediaStorage\Model\File\Uploader',
                ['fileId' => 'data_import_file']
            );

            $fileContent = '';
            if($uploader) {
                $tmpDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::TMP);
                $savePath     = $tmpDirectory->getAbsolutePath('tag/import');
                $uploader->setAllowRenameFiles(true);
                $result       = $uploader->save($savePath);
                $filePath = $tmpDirectory->getAbsolutePath('tag/import/' . $result['file']);
                $fileContent  = file_get_contents($tmpDirectory->getAbsolutePath('tag/import/' . $result['file']));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Can't import data<br/> %1", $e->getMessage()));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }
        $delimiter = $this->getRequest()->getParam('split_symbol');
        if($delimiter) {
            $importData = $this->csvProcessor->setDelimiter($delimiter)->getData($filePath);
        } else {
            $importData = $this->csvProcessor->getData($filePath);
        }
        
        $store = $this->_storeManager->getStore($data['store_id']);
        $connection = $this->_resource->getConnection();
        if(!empty($importData)) {
            try{
                
                $heading = $importData[0];
                unset($importData[0]);

                $brand_index = array_search("Name", $heading);
               

                $connection = $this->_resource->getConnection();
                $tableName = $this->_resource->getTableName('mageplaza_blog_tag');


                


                if($brand_index !== false) {
                    $imported_counter = 0;

                    foreach($importData as $key => $item_data) {

                        $name = $item_data[0];
                        $url = strtolower($item_data[1]);
                        $url = str_replace('-', ' ', $url);
                        $url = preg_replace('/[^A-Za-z0-9\-]/', '', $url);
                        $url = str_replace(' ', '-', $url);
                        $description = $item_data[2];
                        $meta_title = $item_data[3];
                        $meta_description = $item_data[4];
                        $meta_keywords = $item_data[5];
                        $meta_robots = $item_data[6];
                        
                        try{
                            $sql = "Insert Into " . $tableName . " (`name`, `url_key`,`description`, `store_ids`,`enabled`, `meta_title`, `meta_keywords`, `meta_description`, `meta_robots`, `import_source`) Values ('$name', '$url', '$description', '0', 1, '$meta_title', '$meta_keywords', '$meta_description', '$meta_robots', NULL)";

                            $connection->query($sql);
                        } else {
                            $this->messageManager->addError(__("Required there columns: Tag"));
                        }
                        $imported_counter++;
                    }

                    if($imported_counter) 
                        $this->messageManager->addSuccess(__("Import successfully"));
                    else 
                        $this->messageManager->addError(__("Can not found brand item to imported."));
                } else {
                    $this->messageManager->addError(__("Required there columns: Tag"));
                }
            }catch(\Exception $e){
                $this->messageManager->addError(__("Can't import data<br/> %1", $e->getMessage()));
            }


            
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}

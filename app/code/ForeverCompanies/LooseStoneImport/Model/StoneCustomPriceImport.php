<?php

namespace ForeverCompanies\LooseStoneImport\Model;

use Exception;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\AbstractAggregateException;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\BulkException;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CronException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\Exception\Plugin\AuthenticationException as PluginAuthenticationException;
use Magento\Framework\Exception\RemoteServiceUnavailableException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\Exception\SerializationException;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InitException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\TemporaryState\CouldNotSaveException as TemporaryStateCouldNotSaveException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\File\Csv;
use Magento\Catalog\Model\ResourceModel\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class StoneCustomPriceImport
{
    protected Csv $csv;
    protected ProductAction $productAction;
    protected CollectionFactory $productCollection;
    protected Product $productModel;
    protected string $fileName;
    protected ScopeConfigInterface $scopeConfig;
    protected string $storeScope;

    protected ResourceConnection $resourceConnection;
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;

    protected array $csvHeaderMap;
    protected array $requiredFieldsArr;

    protected int $statusEnabled;
    protected int $statusDisabled;

    protected string $logActionSuccess = 'price update';
    protected string $logActionError = 'price error';

    public function __construct(
        Csv $csv,
        ProductAction $action,
        CollectionFactory $productCollection,
        Product $productModel,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resource
    ) {
        $this->csv = $csv;
        $this->productAction = $action;
        $this->productCollection = $productCollection;
        $this->productModel = $productModel;
        $this->fileName = '/var/www/magento/var/import/stone_custom_prices.csv';
        $this->scopeConfig = $scopeConfig;
        $this->storeScope = ScopeInterface::SCOPE_STORE;

        $this->resourceConnection = $resource;
        $this->connection = $resource->getConnection();

        $this->statusEnabled = Status::STATUS_ENABLED;
        $this->statusDisabled = Status::STATUS_DISABLED;

        $this->csvHeaderMap = [
            "Certificate #" => "sku",
            "Price" => "price",
            "Cost" => "cost",
        ];

        $this->requiredFieldsArr = [
            "Certificate #",
            "Price",
            "Cost"
        ];
    }

    /**
     * Execute script - disable all stones listed in given file
     * @throws Exception
     */
    public function run()
    {
        // get data file from FTP server
        $this->updateStoneCustomPricesCsv();

        // generate array of data to be processed from csv file
        $csvArray = $this->buildArray();

        // counter for row count and processed rows
        $totalCount = $count = 0;

        // loop through each record of the csv
        foreach ($csvArray as $csvRow) {
            $totalCount++;
            try {
                // verify all required fields exist in this record, including Certificate #
                // if they do not exist, log error and proceed to next record
                if (!$this->checkForRequiredFields($csvRow)) {
                    echo "err required\n";
                    $product = new DataObject();
                    if (isset($csvRow['Certificate #'])) {
                        $product->setSku($csvRow['Certificate #']);
                    }
                    $this->stoneLog($product, $csvRow, $this->logActionError, "Required field invalid.");
                    continue;
                }

                // see if we have an existing product with the current Certificate #
                // if we do, then we know we are editing an existing product, else throw error
                $productId = $this->productModel->getIdBySku($csvRow['Certificate #']);
                if ($productId) {
                    $product = $this->productModel->load($productId);

                    // if existing product has been disabled assume it has been sold
                    // (or supplier was disabled, which will end up with product being deleted later)
                    if ($product->getStatus() == $this->statusDisabled) {
                        unset($productId);
                        unset($product);
                        continue;
                    }

                    // apply all data from csv to this product and save it
                    foreach ($csvRow as $csvKey => $csvVal) {
                        if (isset($this->csvHeaderMap[$csvKey]) && trim($this->csvHeaderMap[$csvKey]) != "") {
                            $product->setData($this->csvHeaderMap[$csvKey], $csvVal);
                        }
                    }
                    $product->save();

                    $count++;

                    // log success entry
                    $this->stoneLog($product, $csvRow, $this->logActionSuccess);
                } else { // else new product, throw error
                    $product = new DataObject();
                    if (isset($csvRow['Certificate #'])) {
                        $product->setSku($csvRow['Certificate #']);
                    }
                    $this->stoneLog($product, $csvRow, $this->logActionError, "Certificate # does not exist.");
                    continue;
                }
            } catch (
                PluginAuthenticationException | ExpiredException | InitException | InputMismatchException
                | InvalidTransitionException | UserLockedException | TemporaryStateCouldNotSaveException
                | AbstractAggregateException | AlreadyExistsException | AuthenticationException
                | AuthorizationException | BulkException | ConfigurationMismatchException | CouldNotDeleteException
                | CouldNotSaveException | CronException | EmailNotConfirmedException | FileSystemException
                | InputException | IntegrationException | InvalidArgumentExceptionm | InvalidEmailOrPasswordException
                | LocalizedException | MailException | NoSuchEntityException | NotFoundException | PaymentException
                | RemoteServiceUnavailableException | RuntimeException | SecurityViolationException
                | SerializationException | SessionException | StateException | ValidatorException $e
            ) {
                $product = new DataObject();
                if (isset($csvRow['Certificate #'])) {
                    $product->setSku($csvRow['Certificate #']);
                }
                $this->stoneLog(
                    $product,
                    $csvRow,
                    $this->logActionError,
                    $csvRow['Certificate #'] . " not processed. " . $e->getMessage()
                );
            } catch (Exception $e) {
                $product = new DataObject();
                if (isset($csvRow['Certificate #'])) {
                    $product->setSku($csvRow['Certificate #']);
                }
                $this->stoneLog(
                    $product,
                    $csvRow,
                    $this->logActionError,
                    $csvRow['Certificate #'] . " not processed. " . $e->getMessage()
                );
            }
        }

        echo "Done! Total records successfully processed: $count out of $totalCount rows.";
    }

    /**
     * Build array of data to be processed, from the file defined at $this->fileName
     * @return array
     * @throws Exception
     */
    public function buildArray(): array
    {
        $array = [];
        $fields = [];
        $i = 0;

        if (file_exists($this->fileName)) {
            $csvData = $this->csv->getData($this->fileName);
            foreach ($csvData as $k => $val) {
                if ($k == 0) {
                    $fields = $val;
                    continue;
                }
                foreach ($val as $k => $value) {
                    $array[$i][$fields[$k]] = $value;
                }
                $i++;
            }
        }
        return $array;
    }

    /**
     * Verify all required fields are populated with data
     * @param $arr
     * @return bool
     */
    protected function checkForRequiredFields($arr): bool
    {
        foreach ($this->requiredFieldsArr as $req) {
            if (!isset($arr[$req]) || trim($arr[$req]) == "" || $arr[$req] == "Nan") {
                return false;
            }
        }
        return true;
    }

    /**
     * Log actions in DB
     * @param $product
     * @param $csvArr
     * @param $action
     * @param null $error
     */
    protected function stoneLog($product, $csvArr, $action, $error = null)
    {
        if ($error) {
            $query = 'INSERT INTO stone_log(sku, log_action, payload, errors)
                VALUES("' . $product->getSku() . '", "' . $action . '", "' . addslashes(
                    json_encode($csvArr)
                ) . '", "' . $error . '")';
        } else {
            $query = 'INSERT INTO stone_log(sku, log_action, payload)
                VALUES("' . $product->getSku() . '", "' . $action . '", "' . addslashes(
                    json_encode($csvArr)
                ) . '")';
        }
        $this->connection->query($query);
    }

    /**
     * Connect to FTP server and pull down latest disable stones sheet
     */
    protected function updateStoneCustomPricesCsv()
    {
        $ftp = ftp_connect(
            $this->scopeConfig->getValue('forevercompanies_stone_ftp/creds/host', $this->storeScope),
            $this->scopeConfig->getValue('forevercompanies_stone_ftp/creds/port', $this->storeScope)
        );

        $login_result = ftp_login(
            $ftp,
            $this->scopeConfig->getValue('forevercompanies_stone_ftp/creds/user', $this->storeScope),
            $this->scopeConfig->getValue('forevercompanies_stone_ftp/creds/pass', $this->storeScope)
        );
        ftp_pasv($ftp, true);

        $files = ftp_nlist(
            $ftp,
            ftp_pwd($ftp) . DS . $this->scopeConfig->getValue(
                'forevercompanies_stone_ftp/creds/custom_price_pattern',
                $this->storeScope
            )
        );

        foreach ($files as $file) {
            ftp_get($ftp, '/var/www/magento/var/import/stone_custom_prices.csv', $file);
        }

        ftp_close($ftp);
    }
}

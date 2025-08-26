<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;

/**
 * This is the model class for table "expense".
 *
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property string $category
 * @property string|null $description
 * @property string $spent_at
 * @property string $status
 * @property string|null $receipt_path
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 */
class Expense extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * @var UploadedFile
     */
    public $receiptFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%expense}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'amount', 'category', 'spent_at'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number'],
            [['description'], 'string'],
            [['spent_at'], 'date', 'format' => 'php:Y-m-d'],
            [['category'], 'string', 'max' => 64],
            [['status'], 'string', 'max' => 16],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED]],
            [['receipt_path'], 'string', 'max' => 255],
            [['receiptFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User',
            'amount' => 'Amount',
            'category' => 'Category',
            'description' => 'Description',
            'spent_at' => 'Date Spent',
            'status' => 'Status',
            'receipt_path' => 'Receipt',
            'receiptFile' => 'Receipt File',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Upload file if provided
            if ($this->receiptFile instanceof UploadedFile) {
                $directory = Yii::getAlias('@webroot/uploads/receipts');

                if (!file_exists($directory)) {
                    mkdir($directory, 0777, true);
                }

                $filename = 'receipt_' . Yii::$app->security->generateRandomString(8) . '.' . $this->receiptFile->extension;
                $filePath = $directory . '/' . $filename;

                if ($this->receiptFile->saveAs($filePath)) {
                    $this->receipt_path = '/uploads/receipts/' . $filename;
                }
            }

            // If it's a new record and user_id is not set, assign current user
            if ($this->isNewRecord && !$this->user_id) {
                $this->user_id = Yii::$app->user->id;
            }

            return true;
        }
        return false;
    }

    /**
     * Get status badge HTML
     *
     * @return string
     */
    public function getStatusBadge()
    {
        $class = 'secondary';

        switch ($this->status) {
            case self::STATUS_APPROVED:
                $class = 'success';
                break;
            case self::STATUS_REJECTED:
                $class = 'danger';
                break;
            case self::STATUS_PENDING:
                $class = 'warning';
                break;
        }

        return '<span class="badge bg-' . $class . '">' . ucfirst($this->status) . '</span>';
    }

    /**
     * Get available categories
     *
     * @return array
     */
    public static function getCategories()
    {
        return [
            'travel' => 'Travel',
            'meals' => 'Meals & Entertainment',
            'supplies' => 'Office Supplies',
            'utilities' => 'Utilities',
            'transportation' => 'Transportation',
            'accommodation' => 'Accommodation',
            'training' => 'Training & Education',
            'software' => 'Software & Subscriptions',
            'other' => 'Other',
        ];
    }
}
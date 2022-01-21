<?php

namespace common\enums\rbac;

use yii2rails\extension\enum\base\BaseEnum;

/**
 * Class PermissionEnum
 * 
 * Этот класс был сгенерирован автоматически.
 * Не вносите в данный файл изменения, они затрутся при очередной генерации.
 * Изменить набор констант можно через управление RBAC в админке.
 * 
 * @package common\enums\rbac
 */
class PermissionEnum extends BaseEnum {

	// Доступ к REST-клиенту
	const REST_CLIENT_ALL = 'oRestClientAll';

	// Доступ к Yii генератору
	const GII_MANAGE = 'oGiiManage';

	// Управление языками
	const LANG_MANAGE = 'oLangManage';

	// Управление RBAC
	const RBAC_MANAGE = 'oRbacManage';

	// Доступ в админ панель
	const BACKEND_ALL = 'oBackendAll';

	// Управление логами
	const LOGREADER_MANAGE = 'oLogreaderManage';

	// Управление статусом оффлайн
	const OFFLINE_MANAGE = 'oOfflineManage';

	// Управление чистильщиком
	const CLEANER_MANAGE = 'oCleanerManage';

	// Управление уведомлениями
	const NOTIFY_MANAGE = 'oNotifyManage';

	// Шифрование данных
	const ENCRYPT_MANAGE = 'oEncryptManage';

	// Управление композер-пакетами
	const VENDOR_MANAGE = 'oVendorManage';

	// Управление новостями
	const NEWS_MANAGE = 'oNewsManage';

	// Управление файлами
	const FILE_MANAGE = 'oFileManage';

	// Редактирование руководства
	const GUIDE_MODIFY = 'oGuideModify';

	// Управление новостями
	const NEWS_NEWS_MANAGE = 'oNewsNewsManage';

	// Управление комментариями
	const NEWS_COMMENT_MANAGE = 'oNewsCommentManage';

	// Управление корпоративной частью почты
	const STAFF_MANAGER = 'oStaffManager';

	// Отправлять письма от любого пользователя
	const SEND_CUSTOM_EMAIL_MESSAGE = 'oSendCustomEmailMessage';

	// Управление файловым хранилищем
	const STORAGE_MANAGE = 'oStorageManage';

	const ACCOUNT_MANAGE = 'oAccountManage';

}
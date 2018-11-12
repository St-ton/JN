<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin\Admin\Validation\Items;

use Plugin\InstallCode;

/**
 * Class PaymentMethods
 * @package Plugin\Admin\Validation\Items
 */
class PaymentMethods extends AbstractItem
{
    /**
     * @inheritdoc
     */
    public function validate(): int
    {
        $node = $this->getInstallNode();
        $dir  = $this->getDir();
        if (!isset($node['PaymentMethod'][0]['Method'])
            || \is_array($node['PaymentMethod'][0]['Method'])
        ) {
            return InstallCode::OK;
        }
        foreach ($node['PaymentMethod'][0]['Method'] as $u => $method) {
            \preg_match('/[0-9]+\sattr/', $u, $hits1);
            \preg_match('/[0-9]+/', $u, $hits2);
            if (\strlen($hits2[0]) !== \strlen($u)) {
                continue;
            }
            \preg_match(
                "/[a-zA-Z0-9äÄöÖüÜß" . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                $method['Name'],
                $hits1
            );
            if (\strlen($hits1[0]) !== \strlen($method['Name'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_NAME;
            }
            \preg_match('/[0-9]+/', $method['Sort'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($method['Sort'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_SORT;
            }
            \preg_match("/[0-1]{1}/", $method['SendMail'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($method['SendMail'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_MAIL;
            }
            \preg_match('/[A-Z_]+/', $method['TSCode'], $hits1);
            if (\strlen($hits1[0]) === \strlen($method['TSCode'])) {
                $cTSCode_arr = [
                    'DIRECT_DEBIT',
                    'CREDIT_CARD',
                    'INVOICE',
                    'CASH_ON_DELIVERY',
                    'PREPAYMENT',
                    'CHEQUE',
                    'PAYBOX',
                    'PAYPAL',
                    'CASH_ON_PICKUP',
                    'FINANCING',
                    'LEASING',
                    'T_PAY',
                    'GIROPAY',
                    'GOOGLE_CHECKOUT',
                    'SHOP_CARD',
                    'DIRECT_E_BANKING',
                    'OTHER'
                ];
                if (!\in_array($method['TSCode'], $cTSCode_arr, true)) {
                    return InstallCode::INVALID_PAYMENT_METHOD_TSCODE;
                }
            } else {
                return InstallCode::INVALID_PAYMENT_METHOD_TSCODE;
            }
            \preg_match("/[0-1]{1}/", $method['PreOrder'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($method['PreOrder'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_PRE_ORDER;
            }
            \preg_match("/[0-1]{1}/", $method['Soap'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($method['Soap'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_SOAP;
            }
            \preg_match("/[0-1]{1}/", $method['Curl'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($method['Curl'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_CURL;
            }
            \preg_match('/[0-1]{1}/', $method['Sockets'], $hits1);
            if (\strlen($hits1[0]) !== \strlen($method['Sockets'])) {
                return InstallCode::INVALID_PAYMENT_METHOD_SOCKETS;
            }
            if (isset($method['ClassFile'])) {
                \preg_match('/[a-zA-Z0-9\/_\-.]+.php/', $method['ClassFile'], $hits1);
                if (\strlen($hits1[0]) === \strlen($method['ClassFile'])) {
                    if (!\file_exists($dir . \PFAD_PLUGIN_PAYMENTMETHOD . $method['ClassFile'])) {
                        return InstallCode::MISSING_PAYMENT_METHOD_FILE;
                    }
                } else {
                    return InstallCode::INVALID_PAYMENT_METHOD_CLASS_FILE;
                }
            }
            if (isset($method['ClassName'])) {
                \preg_match("/[a-zA-Z0-9\/_\-]+/", $method['ClassName'], $hits1);
                if (\strlen($hits1[0]) !== \strlen($method['ClassName'])) {
                    return InstallCode::INVALID_PAYMENT_METHOD_CLASS_NAME;
                }
            }
            if (isset($method['TemplateFile']) && \strlen($method['TemplateFile']) > 0) {
                \preg_match(
                    '/[a-zA-Z0-9\/_\-.]+.tpl/',
                    $method['TemplateFile'],
                    $hits1
                );
                if (\strlen($hits1[0]) !== \strlen($method['TemplateFile'])) {
                    return InstallCode::INVALID_PAYMENT_METHOD_TEMPLATE;
                }
                if (!\file_exists($dir . \PFAD_PLUGIN_PAYMENTMETHOD . $method['TemplateFile'])) {
                    return InstallCode::MISSING_PAYMENT_METHOD_TEMPLATE;
                }
            }
            if (isset($method['AdditionalTemplateFile']) && \strlen($method['AdditionalTemplateFile']) > 0) {
                \preg_match(
                    '/[a-zA-Z0-9\/_\-.]+.tpl/',
                    $method['AdditionalTemplateFile'],
                    $hits1
                );
                if (\strlen($hits1[0]) !== \strlen($method['AdditionalTemplateFile'])) {
                    return InstallCode::INVALID_PAYMENT_METHOD_ADDITIONAL_STEP_TEMPLATE_FILE;
                }
                if (!\file_exists($dir . \PFAD_PLUGIN_PAYMENTMETHOD . $method['AdditionalTemplateFile'])) {
                    return InstallCode::MISSING_PAYMENT_METHOD_ADDITIONAL_STEP_FILE;
                }
            }
            if (!isset($method['MethodLanguage'])
                || !\is_array($method['MethodLanguage'])
                || \count($method['MethodLanguage']) === 0
            ) {
                return InstallCode::MISSING_PAYMENT_METHOD_LANGUAGES;
            }
            foreach ($method['MethodLanguage'] as $l => $localized) {
                \preg_match('/[0-9]+\sattr/', $l, $hits1);
                \preg_match('/[0-9]+/', $l, $hits2);
                if (isset($hits1[0]) && \strlen($hits1[0]) === \strlen($l)) {
                    \preg_match("/[A-Z]{3}/", $localized['iso'], $hits);
                    $len = \strlen($localized['iso']);
                    if ($len === 0 || \strlen($hits[0]) !== $len) {
                        return InstallCode::INVALID_PAYMENT_METHOD_LANGUAGE_ISO;
                    }
                } elseif (isset($hits2[0]) && \strlen($hits2[0]) === \strlen($l)) {
                    if (!isset($localized['Name'])) {
                        return InstallCode::INVALID_PAYMENT_METHOD_NAME_LOCALIZED;
                    }
                    \preg_match(
                        "/[a-zA-Z0-9äÄöÖüÜß" . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                        $localized['Name'],
                        $hits1
                    );
                    if (\strlen($hits1[0]) !== \strlen($localized['Name'])) {
                        return InstallCode::INVALID_PAYMENT_METHOD_NAME_LOCALIZED;
                    }
                    if (!isset($localized['ChargeName'])) {
                        return InstallCode::INVALID_PAYMENT_METHOD_CHARGE_NAME;
                    }
                    \preg_match(
                        "/[a-zA-Z0-9äÄöÖüÜß" . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                        $localized['ChargeName'],
                        $hits1
                    );
                    if (\strlen($hits1[0]) !== \strlen($localized['ChargeName'])) {
                        return InstallCode::INVALID_PAYMENT_METHOD_CHARGE_NAME;
                    }
                    if (!isset($localized['InfoText'])) {
                        return InstallCode::INVALID_PAYMENT_METHOD_INFO_TEXT;
                    }
                    \preg_match(
                        "/[a-zA-Z0-9äÄöÖüÜß" . "\.\,\!\"\§\$\%\&\/\(\)\=\`\´\+\~\*\'\;\-\_\?\{\}\[\] ]+/",
                        $localized['InfoText'],
                        $hits1
                    );
                    if (isset($hits1[0]) && \strlen($hits1[0]) !== \strlen($localized['InfoText'])) {
                        return InstallCode::INVALID_PAYMENT_METHOD_INFO_TEXT;
                    }
                }
            }
            $type = '';
            if (!isset($method['Setting']) || !\is_array($method['Setting']) || !\count($method['Setting']) === 0) {
                continue;
            }
            foreach ($method['Setting'] as $j => $setting) {
                \preg_match('/[0-9]+\sattr/', $j, $hits3);
                \preg_match('/[0-9]+/', $j, $hits4);
                if (isset($hits3[0]) && \strlen($hits3[0]) === \strlen($j)) {
                    $type = $setting['type'];
                    if (\strlen($setting['type']) === 0) {
                        return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_TYPE;
                    }
                    if (\strlen($setting['sort']) === 0) {
                        return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_SORT;
                    }
                    if (\strlen($setting['conf']) === 0) {
                        return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_CONF;
                    }
                } elseif (isset($hits4[0]) && \strlen($hits4[0]) === \strlen($j)) {
                    if (\strlen($setting['Name']) === 0) {
                        return InstallCode::INVALID_PAYMENT_METHOD_CONFIG_NAME;
                    }
                    if (\strlen($setting['ValueName']) === 0) {
                        return InstallCode::INVALID_PAYMENT_METHOD_VALUE_NAME;
                    }
                    if ($type === 'selectbox') {
                        if (!isset($setting['SelectboxOptions'])
                            || !\is_array($setting['SelectboxOptions'])
                            || \count($setting['SelectboxOptions']) === 0
                        ) {
                            return InstallCode::MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS;
                        }
                        if (\count($setting['SelectboxOptions'][0]) === 1) {
                            foreach ($setting['SelectboxOptions'][0]['Option'] as $y => $options) {
                                \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                \preg_match('/[0-9]+/', $y, $hits7);
                                if (isset($hits6[0]) && \strlen($hits6[0]) === \strlen($y)) {
                                    if (\strlen($options['value']) === 0) {
                                        return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                    if (\strlen($options['sort']) === 0) {
                                        return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                } elseif (isset($hits7[0]) && \strlen($hits7[0]) === \strlen($y)) {
                                    if (\strlen($options) === 0) {
                                        return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                }
                            }
                        } elseif (\count($setting['SelectboxOptions'][0]) === 2) {
                            //Es gibt nur 1 Option
                            if (\strlen($setting['SelectboxOptions'][0]['Option attr']['value']) === 0) {
                                return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                            }
                            if (\strlen($setting['SelectboxOptions'][0]['Option attr']['sort']) === 0) {
                                return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                            }
                            if (\strlen($setting['SelectboxOptions'][0]['Option']) === 0) {
                                return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                            }
                        }
                    } elseif ($type === 'radio') {
                        if (!isset($setting['RadioOptions'])
                            || !\is_array($setting['RadioOptions'])
                            || \count($setting['RadioOptions']) === 0
                        ) {
                            return InstallCode::MISSING_PAYMENT_METHOD_SELECTBOX_OPTIONS;
                        }
                        if (\count($setting['RadioOptions'][0]) === 1) {
                            foreach ($setting['RadioOptions'][0]['Option'] as $y => $options) {
                                \preg_match('/[0-9]+\sattr/', $y, $hits6);
                                \preg_match('/[0-9]+/', $y, $hits7);
                                if (isset($hits6[0]) && \strlen($hits6[0]) === \strlen($y)) {
                                    if (\strlen($options['value']) === 0) {
                                        return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                    if (\strlen($options['sort']) === 0) {
                                        return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                } elseif (isset($hits7[0]) && \strlen($hits7[0]) === \strlen($y)) {
                                    if (\strlen($options) === 0) {
                                        return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                                    }
                                }
                            }
                        } elseif (\count($setting['RadioOptions'][0]) === 2) { //Es gibt nur 1 Option
                            if (\strlen($setting['RadioOptions'][0]['Option attr']['value']) === 0) {
                                return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                            }
                            if (\strlen($setting['RadioOptions'][0]['Option attr']['sort']) === 0) {
                                return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                            }
                            if (\strlen($setting['RadioOptions'][0]['Option']) === 0) {
                                return InstallCode::INVALID_PAYMENT_METHOD_OPTION;
                            }
                        }
                    }
                }
            }
        }

        return InstallCode::OK;
    }
}

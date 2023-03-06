<?php declare(strict_types=1);

namespace JTL\REST\Models;

/**
 * Class LanguageModel
 *
 * @package JTL\Language
 * @OA\Schema(
 *     title="Language model",
 *     description="Language model",
 * )
 * @OA\Property(
 *   property="id",
 *   type="integer",
 *   example=33,
 *   description="The language ID"
 * )
 * @property int    $id
 * @property int    $kSprache
 * @OA\Property(
 *   property="active",
 *   type="integer",
 *   example=1,
 *   description="1=active, 0=inactive"
 * )
 * @property int    $active
 * @OA\Property(
 *   property="nameEN",
 *   type="string",
 *   example="German",
 *   description="The english translation of the language's name"
 * )
 * @property string $nameEN
 * @property string $cNameEnglisch
 * @OA\Property(
 *   property="nameDE",
 *   type="string",
 *   example="Deutsch",
 *   description="The german translation of the language's name"
 * )
 * @property string $nameDE
 * @property string $cNameDeutsch
 * @OA\Property(
 *   property="default",
 *   type="string",
 *   example="Y",
 *   description="Y=default language, N=non-default language"
 * )
 * @property string $default
 * @property string $cStandard
 * @OA\Property(
 *   property="shopDefault",
 *   type="string",
 *   example="Y",
 *   description="Y=default shop language, N=non-default shop language"
 * )
 * @property string $shopDefault
 * @property string $cShopStandard
 * @OA\Property(
 *   property="iso639",
 *   type="string",
 *   example="ger",
 *   description="Locale code in ISO639 form"
 * )
 * @property string $cISO
 * @property string $iso639
 */
class LanguageModel
{

}

<?php

namespace Faker\Provider\ar_SA;

use Faker\Calculator\Luhn;

class Person extends \Faker\Provider\Person
{
    protected static $maleNameFormats = array(
        '{{firstNameMale}} {{lastName}}',
        '{{firstNameMale}} {{lastName}}',
        '{{firstNameMale}} {{lastName}}',
        '{{firstNameMale}} {{firstNameMale}} {{lastName}}',
        '{{firstNameMale}} {{firstNameMale}} {{firstNameMale}} {{lastName}}',
        '{{titleFemale}} {{firstNameFemale}} {{lastName}}',
    );

    protected static $femaleNameFormats = array(
        '{{firstNameFemale}} {{lastName}}',
        '{{firstNameFemale}} {{lastName}}',
        '{{firstNameFemale}} {{lastName}}',
        '{{firstNameFemale}} {{lastName}}',
        '{{firstNameFemale}} {{firstNameMale}} {{lastName}}',
        '{{firstNameFemale}} {{firstNameMale}} {{firstNameMale}} {{lastName}}',
        '{{titleFemale}} {{firstNameFemale}} {{lastName}}',
    );

    /**
     * @link http://muslim-names.us/
     */
    protected static $firstNameMale = array(

        'آدم', 'أبراهيم', 'أحمد', 'أدهم', 'أسامة', 'أسعد', 'أشرف', 'أكثم', 'أكرم', 'أمجد', 'أمين', 'أنس', 'أنور', 'أواس', 'أوس', 'أيمن', 'أيهم', 'أيوب', 'إبراهيم', 'إسلام', 'إسماعيل', 'إلياس', 'إياد', 'إيهاب', 'ابان', 'ابراهيم', 'اثير', 'احسان', 'احمد', 'ادريس', 'ادم', 'ادهم', 'اديب', 'اسامة',
        'اسحاق', 'اسحق', 'اسعد', 'اسلام', 'اسماعيل', 'اسيد', 'اشراف', 'اشرف', 'اصلان', 'اكثم', 'اكرم', 'البراء', 'البشر', 'الحارث', 'الحسين', 'الطفيل', 'العزم', 'الليث', 'المثنى', 'المنصور', 'الياس', 'اليمان', 'امجد', 'امير', 'امين', 'أنس', 'انور', 'انيس', 'اوس', 'اوسم', 'اويس', 'اياد', 'اياس',
        'ايسر', 'ايمن', 'ايهم', 'ايوب', 'باسل', 'باسم', 'باهر', 'بدر', 'بدوان', 'براء', 'برهان', 'بسام', 'بشار', 'بشر', 'بشير', 'بكر', 'بلال', 'بليغ', 'بندر', 'بهاء', 'تركي', 'توفيق', 'ثامر', 'جابر', 'جاسر', 'جاسم', 'جبر', 'جبير', 'جراح', 'جريس', 'جعفر', 'جلال', 'جمال', 'جمزه', 'جميل', 'جهاد',
        'جواد', 'حابس', 'حاتم', 'حارث', 'حازم', 'حافظ', 'حاكم', 'حامد', 'حبيب', 'حذيفة', 'حسام', 'حسان', 'حسن', 'حسني', 'حسين', 'حكم', 'حمد', 'حمدالله', 'حمدان', 'حمدي', 'حمزة', 'حمود', 'حميد', 'خالد', 'خضر', 'خلدون', 'خلف', 'خليفة', 'خليل', 'خميس', 'داوود', 'ذياب', 'ذيب', 'رأفت', 'رؤوف', 'رئاد',
        'رائد', 'رائف', 'راجح', 'راجي', 'راشد', 'راضي', 'راغب', 'رافت', 'رافع', 'رافي', 'راكان', 'رامان', 'رامز', 'رامي', 'رامين', 'ربيع', 'رجا', 'رجائي', 'رجب', 'رداد', 'رزق', 'رسلان', 'رشاد', 'رشيد', 'رضا', 'رضوان', 'رعد', 'رغد', 'رغيد', 'ركان', 'رماح', 'رياض', 'ريان', 'زاهر', 'زاهي', 'زايد',
        'زكريا', 'زمام', 'زهير', 'زياد', 'زيد', 'زيدان', 'زيدون', 'زين', 'زين العابدين', 'سائد', 'ساري', 'سالم', 'سامح', 'سامر', 'سامي', 'ساهر', 'سدير', 'سرمد', 'سري', 'سعد', 'سعود', 'سعيد', 'سفيان', 'سكوت', 'سلام', 'سلطان', 'سلمان', 'سليم', 'سليمان', 'سمعان', 'سميح', 'سنان', 'سند', 'سهل', 'سهم',
        'سيف', 'شادي', 'شافع', 'شاكر', 'شامل', 'شاهر', 'شرحبيل', 'شريف', 'شهاب', 'شهم', 'شوان', 'صادق', 'صافي', 'صالح', 'صخر', 'صدام', 'صفاء', 'صفوان', 'صقر', 'صلاح', 'صلاح الدين', 'صهيب', 'ضرار', 'ضرغام', 'ضياء', 'ضياء الدين’, ', 'طارق', 'طالب', 'طاهر', 'طلال', 'طه', 'عادل', 'عاصم', 'عاطف',
        'عامر', 'عايد', 'عبادة', 'عباس', 'عبد الباري', 'عبد الحافظ', 'عبد الحكيم', 'عبد الحليم', 'عبد الحميد', 'عبد الحي', 'عبد الرحمان', 'عبد الرحمن', 'عبد الرحيم', 'عبد الرزاق', 'عبد السلام', 'عبد السميع', 'عبد العزيز', 'عبد العفو', 'عبد الغني', 'عبد الفتاح', 'عبد القادر', 'عبد الكريم',
        'عبد اللطيف', 'عبد الله', 'عبد المجيد', 'عبد المولى', 'عبد الناصر', 'عبد الهادي', 'عبد ربه', 'عبداالله', 'عبدالاله', 'عبدالباسط', 'عبدالجليل', 'عبدالجواد', 'عبدالحليم', 'عبدالحميد', 'عبدالرؤوف', 'عبدالرحمن', 'عبدالرحيم', 'عبدالرزاق', 'عبدالسلام', 'عبدالعزيز', 'عبدالفتاح', 'عبدالقادر',
        'عبدالكريم', 'عبداللطيف', 'عبدالله', 'عبدالمجيد', 'عبدالمطلب', 'عبدالمعطي', 'عبدالمهيمن', 'عبدالناصر', 'عبدالهادي', 'عبدالوهاب', 'عبيدالله', 'عبيدة', 'عتيبه', 'عثمان', 'عدب', 'عدلي', 'عدنان', 'عدوان', 'عدي', 'عرار', 'عرمان', 'عروة', 'عريق', 'عرين', 'عز الدين', 'عزالدين', 'عزام', 'عزت',
        'عزمي', 'عزيز', 'عصام', 'عقل', 'علاء', 'علي', 'عليان', 'عماد', 'عمار', 'عمر', 'عمران', 'عمرو', 'عملا', 'عميد', 'عناد', 'عنان', 'عواد', 'عودة', 'عوده', 'عوض', 'عوف', 'عون', 'عوني', 'عيد', 'عيدالله', 'عيسى', 'غازي', 'غالب', 'غانم', 'غدير', 'غسان', 'غيث', 'فؤاد', 'فائق', 'فاخر', 'فادي',
        'فارس', 'فاروق', 'فاضل', 'فايز', 'فتحي', 'فجر', 'فراس', 'فرح', 'فريد', 'فلاح', 'فهد', 'فهمي', 'فواز', 'فوزي', 'فيصل', 'قارس', 'قاسم', 'قبلان', 'قتاده', 'قتيبة', 'قصي', 'قيس', 'كاظم', 'كامل', 'كايد', 'كرم', 'كريم', 'كفاح', 'كمال', 'كنان', 'لؤي', 'لبيب', 'لطف', 'لطفي', 'ليث', 'مأمون', 'مؤثر',
        'مؤمن', 'مؤنس', 'مؤيد', 'ماجد', 'مازن', 'مالك', 'مامون', 'ماهر', 'مبارك', 'مثنى', 'مجاهد', 'مجد', 'مجدي', 'محسن', 'محمد ', 'محمود', 'محي', 'مختار', 'مخلص', 'مدحت', 'مدين', 'مراد', 'مرشد', 'مرهف', 'مروان', 'مسعد', 'مسعود', 'مسلم', 'مشاري', 'مشعل', 'مشهور', 'مصباح', 'مصطفى', 'مصطفي', 'مصعب',
        'مضر', 'مطيع', 'مظفر', 'مظهر', 'معاذ', 'معاوية', 'معتز', 'معتصم', 'معمر', 'معن', 'معين', 'مفدي', 'مفلح', 'مقداد', 'ملهم', 'ممدوح', 'مناف', 'منتصر', 'منح', 'منذر', 'منصف', 'منصور', 'منير', 'مهاب', 'مهدي', 'مهران', 'مهند', 'موسى', 'موفق', 'نائل', 'ناجي', 'نادر', 'ناصر', 'ناهض', 'نايف',
        'نبراس', 'نبيل', 'نجيب', 'نديم', 'نزار', 'نزال', 'نزيه', 'نسيم', 'نشات', 'نصار', 'نصر', 'نصري', 'نصوح', 'نضال', 'نظام', 'نعمان', 'نعمة', 'نعيم', 'نقولا', 'نمر', 'نهاد', 'نهار', 'نواف', 'نورس', 'نوفان', 'هادي', 'هارون', 'هاشم', 'هانى', 'هاني', 'هذال', 'هشام', 'هلال', 'همام', 'هيثم', 'وائل',
        'واثق', 'واصف', 'وجدي', 'وجيه', 'وحيد', 'وديع', 'ورد', 'وسام', 'وسن', 'وسيم', 'وصفي', 'وضاح', 'وعد', 'وفاء', 'وليد', 'وهيب', 'ياسر', 'ياسين', 'يامن', 'يحيى', 'يزن', 'يزيد', 'يسار', 'يشار', 'يعرب', 'يعقوب', 'يمان', 'ينال', 'يوسف', 'يونس',
    );

    /**
     * @link http://muslim-names.us/
     */
    protected static $firstNameFemale = array(
        'آثار', 'آلاء', 'آناء', 'آية', 'أبرار', 'أحلام', 'أروى', 'أريج', 'أسماء', 'أسيل', 'أصاله', 'أفنان', 'ألاء', 'أماني', 'أمل', 'أميرة', 'أنسام', 'أنوار', 'إباء', 'إخلاص', 'إسراء', 'إسلام', 'إكرام', 'إنعام', 'إيمان', 'إيناس', 'ابتهاج', 'ابتهال', 'أبرار', 'إخلاص', 'ارجوان', 'أروى', 'أريج',
        'أزهار', 'أسحار', 'اسراء', 'اسرار', 'اسيل', 'اشراق', 'أصالة', 'اعتدال', 'أفراح', 'أفنان', 'إكرام', 'آلاء', 'العنود', 'إلهام', 'آمال', 'أمنة', 'أميرة', 'أمينة', 'أناهيد', 'انتظار', 'أنعام', 'أنوار', 'آيات', 'إيمان', 'إيناس', 'آية', 'باسمة', 'بتول', 'بثينة', 'بدور', 'براء', 'براءة', 'بسمة',
        'بشائر', 'بشرى', 'بلسم', 'بنان', 'بهجة', 'بيان', 'بيداء', 'بيسان', 'تالا', 'تالة', 'تالين', 'تحرير', 'تسنيم', 'تغريد', 'تقوى', 'تقى', 'تمارا', 'تماضر', 'تمام', 'تهاني', 'تولين', 'ثريا', 'جمانة', 'جميلة', 'جنى', 'جهاد', 'جود', 'حبيبة', 'حسناء', 'حصة', 'حلا', 'حليمة', 'حنان', 'حنين', 'حياة',
        'ختام', 'خديجة', 'خلود', 'خولة', 'دارين', 'داليا', 'دالية', 'دانا', 'دانة', 'دانية', 'دعاء', 'دلال', 'دنى', 'دنيا', 'ديانا', 'ديما', 'دينا', 'رؤى', 'رؤيه', 'رابعة', 'راغدة', 'راما', 'رانا', 'رانيا', 'راوية', 'راية', 'ربا', 'رباب', 'ربى', 'رجاء', 'رحمة', 'رحمه', 'ردينة', 'رزان',
        'رشا', 'رغد', 'رغدة', 'رفاه', 'رقية', 'رمال', 'رنا', 'رناد', 'رند', 'رنده', 'رنيم', 'رنين', 'رهام', 'رهف', 'رواء', 'روان', 'روزان', 'روزانا', 'روزين', 'رولى', 'روند', 'رويدة', 'ريان', 'ريتا', 'ريم', 'ريما', 'ريمان', 'ريناتا', 'ريناد', 'ريهام', 'زكية', 'زمان', 'زها', 'زهرة', 'زين', 'زينا',
        'زينات', 'زينب', 'زينة', 'ساجدة', 'سارة', 'سجى', 'سحر', 'سدين', 'سرى', 'سرين', 'سعاد', 'سكينة', 'سلام', 'سلسبيل', 'سلمى', 'سلوى', 'سما', 'سماح', 'سماره', 'سمر', 'سمية', 'سميرة', 'سناء', 'سنابل', 'سندس', 'سنريت', 'سنن', 'سهاد', 'سهام', 'سهر', 'سهى', 'سهير', 'سهيله', 'سوار', 'سوزان', 'سوسن',
        'سيرين', 'سيرينا', 'سيلفا', 'سيلين', 'سيما', 'شذى', 'شروق', 'شريفة', 'شرين', 'شريهان', 'شفاء', 'شهد', 'شيرين', 'شيماء', 'صابرين', 'صبا', 'صباح', 'صبرين', 'صفا', 'صفاء', 'صفية', 'صمود', 'ضحى', 'ضياء', 'عائشة', 'عاليا', 'عالية', 'عبلة', 'عبير', 'عزة', 'عزيزة', 'عفاف', 'علا', 'علياء',
        'عنود', 'عهد', 'غادة', 'غدير', 'غرام', 'غزل', 'غصون', 'غفران', 'غنى', 'غيد', 'غيداء', 'غيده', 'فاتن', 'فادية', 'فاديه', 'فاطمة', 'فايزة', 'فتحية', 'فداء', 'فدوى', 'فدى', 'فرح', 'فريال', 'فريدة', 'فوزية', 'فيروز', 'فيفيان', 'قمر', 'كيان', 'لارا', 'لانا', 'لبنا', 'لجين', 'لطيفة', 'لمى',
        'لميس', 'لنا', 'لورا', 'لورينا', 'لونا', 'ليان', 'ليدا', 'ليلى', 'ليليان', 'لين', 'لينا', 'لينة', 'ليندا', 'لينه', 'مايا', 'مجد', 'مجدولين', 'محبوبة', 'مديحة', 'مرام', 'مرح', 'مروة', 'مريام', 'مريم', 'مسعدة', 'مشيرة', 'معالي', 'ملاك', 'ملك', 'منار', 'منال', 'منى', 'مها', 'مي',
        'ميادة', 'مياده', 'ميار', 'ميان', 'ميرا', 'ميرال', 'ميران', 'ميرفت', 'ميس', 'ميسا', 'ميساء', 'ميسر', 'ميسره', 'ميسم', 'ميسون', 'ميلاء', 'ميناس', 'نائله', 'ناديا', 'نادية', 'نادين', 'ناديه', 'نانسي', 'نبال', 'نبراس', 'نبيله', 'نجاة', 'نجاح', 'نجلاء', 'نجود', 'نجوى', 'نداء', 'ندى',
        'ندين', 'نرمين', 'نسرين', 'نسيمة', 'نعمت', 'نعمه', 'نهاد', 'نهى', 'نهيدة', 'نوال', 'نور', 'نور الهدى', 'نورا', 'نوران', 'نيروز', 'نيفين', 'هادلين', 'هازار', 'هالة', 'هانيا', 'هايدي', 'هبة', 'هدايه', 'هدى', 'هديل', 'هزار', 'هلا', 'هنا', 'هناء', 'هنادي', 'هند', 'هيا', 'هيفا',
        'هيفاء', 'هيلين', 'وئام', 'وجدان', 'وداد', 'ورود', 'وسام', 'وسن', 'وسيم', 'وعد', 'وفاء', 'ولاء', 'ىمنة', 'يارا', 'ياسمين', 'يسرى',
    );

    protected static $lastName = array(
        'العتيبي', 'الشهري', 'العنزي', 'الخضيري', 'الحسين', 'العسكر', 'باشا', 'مدني', 'العرفج',
        'القحطاني', 'الفدا', 'المشيقح', 'العمرو', 'السالم', 'الشيباني', 'السهلي', 'المطرفي',
        'الأحمري', 'الفيفي', 'العقل', 'الفرحان', 'الحصين', 'الأسمري', 'الماجد', 'الخالدي', 'السيف',
        'الحنتوشي', 'الشهيل', 'الزامل', 'الصامل', 'السماعيل', 'الجريد', 'الحميد', 'المقبل',
        'الراجحي', 'المنيف', 'السويلم', 'السمير', 'الصقير', 'الصقيه', 'سقا', 'مكي', 'جواهرجي',
        'الجهني', 'الفريدي', 'برماوي', 'هوساوي', 'السعيد', 'الداوود', 'السليم', 'السماري',
    );

    protected static $titleMale = array('السيد', 'الأستاذ', 'الدكتور', 'المهندس');
    protected static $titleFemale = array('السيدة', 'الآنسة', 'الدكتورة', 'المهندسة');
    private static $prefix = array('أ.', 'د.', 'أ.د', 'م.');

    /**
     * @example 'أ.'
     */
    public static function prefix()
    {
        return static::randomElement(static::$prefix);
    }

    /**
     * @example 1010101010
     */
    public static function idNumber()
    {
        $partialValue = static::numerify(
            static::randomElement(array(1, 2)) . str_repeat('#', 8)
        );
        return Luhn::generateLuhnNumber($partialValue);
    }

    /**
     * @example 1010101010
     */
    public static function nationalIdNumber()
    {
        $partialValue = static::numerify(1 . str_repeat('#', 8));
        return Luhn::generateLuhnNumber($partialValue);
    }

    /**
     * @example 2010101010
     */
    public static function foreignerIdNumber()
    {
        $partialValue = static::numerify(2 . str_repeat('#', 8));
        return Luhn::generateLuhnNumber($partialValue);
    }
}

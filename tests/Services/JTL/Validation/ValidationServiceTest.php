<?php

namespace Tests\Services\JTL\Validation;

use JTL\Services\JTL\Validation\RuleSet;
use JTL\Services\JTL\Validation\ValidationService;
use Tests\BaseTestCase;

/**
 * Class ValidationServiceTest
 * @package Services\JTL\Validation
 */
class ValidationServiceTest extends BaseTestCase
{
    public function test_validate_happyPath()
    {
        $set = new RuleSet();
        $set->email();

        $vs = new ValidationService([], [], []);
        $vs->setRuleSet('email', $set);

        $mail   = 'martin.schophaus@jtl-software.com';
        $result = $vs->validate($mail, 'email');
        $this->assertTrue($result->isValid());
        $this->assertCount(1, $result->getRuleResults());
        $this->assertEquals($mail, $result->getValue());
        $result = $vs->validate($mail, $set);
        $this->assertTrue($result->isValid());
        $this->assertCount(1, $result->getRuleResults());
        $this->assertEquals($mail, $result->getValue());

        $mail   = 'martin.schophaus@ jtl-software.com';
        $result = $vs->validate($mail, 'email');
        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getRuleResults());
        $this->assertEquals('invalid email', $result->getRuleResults()[0]->getMessageId());
        $this->assertEquals($mail, $result->getValueInsecure());
        $this->assertNull($result->getValue());
        $result = $vs->validate($mail, $set);
        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getRuleResults());
        $this->assertEquals('invalid email', $result->getRuleResults()[0]->getMessageId());
        $this->assertEquals($mail, $result->getValueInsecure());
        $this->assertNull($result->getValue());

        $set = new RuleSet();
        $set->numeric()->between(10, 20);
        $this->assertFalse($vs->validate('10b', $set)->isValid());
        $this->assertFalse($vs->validate('8', $set)->isValid());
        $this->assertTrue($vs->validate('12', $set)->isValid());
        $set->integer();
        $this->assertFalse($vs->validate('12.5', $set)->isValid());
        $this->assertTrue($vs->validate('12', $set)->isValid());
    }

    public function test_gpc_happyPath()
    {
        $gpc = ['cMail' => 'martin.schophaus@jtl-software.com'];
        $vs  = new ValidationService($gpc, $gpc, $gpc);
        $vs->setRuleSet('email', (new RuleSet())->email());
        $this->assertTrue($vs->validateGet('cMail', 'email')->isValid());
        $this->assertTrue($vs->validatePost('cMail', 'email')->isValid());
        $this->assertTrue($vs->validateCookie('cMail', 'email')->isValid());

        $gpc = ['cMail' => 'martin.schophaus@ jtl-software.com'];
        $vs  = new ValidationService($gpc, $gpc, $gpc);
        $vs->setRuleSet('email', (new RuleSet())->email());
        $this->assertFalse($vs->validateGet('cMail', 'email')->isValid());
        $this->assertFalse($vs->validatePost('cMail', 'email')->isValid());
        $this->assertFalse($vs->validateCookie('cMail', 'email')->isValid());
    }

    public function test_hasGet()
    {
        $vs = new ValidationService(['mail' => 'test@test.de'], [], []);
        $this->assertTrue($vs->hasGet('mail'));
        $this->assertFalse($vs->hasGet('mail2'));
    }

    public function test_hasPost()
    {
        $vs = new ValidationService([], ['mail' => 'test@test.de'], []);
        $this->assertTrue($vs->hasPost('mail'));
        $this->assertFalse($vs->hasPost('mail2'));
    }

    public function test_hasCookie()
    {
        $vs = new ValidationService([], [], ['mail' => 'test@test.de']);
        $this->assertTrue($vs->hasCookie('mail'));
        $this->assertFalse($vs->hasCookie('mail2'));
    }

    public function test_hasGPC()
    {
        $vs = new ValidationService([], ['mail' => 'ja'], []);
        $this->assertTrue($vs->hasGPC('mail'));
        $this->assertFalse($vs->hasGPC('mail2'));

        $vs = new ValidationService([], [], ['mail' => 'ja']);
        $this->assertTrue($vs->hasGPC('mail'));
        $this->assertFalse($vs->hasGPC('mail2'));
    }

    public function test_hasGP()
    {
        $vs = new ValidationService([], ['mail' => 'ja'], []);
        $this->assertTrue($vs->hasGP('mail'));
        $this->assertFalse($vs->hasGP('mail2'));

        $vs = new ValidationService([], [], ['mail' => 'ja']);
        $this->assertFalse($vs->hasGP('mail'));
    }

    public function test_validateSet_happyPath()
    {
        $keySet  = (new RuleSet())->integer()->gt(0);
        $mailSet = (new RuleSet())->email();

        $rulesConfig = [
            'userId' => $keySet,
            'email'  => $mailSet,
        ];
        $array       = [
            'userId' => 10,
            'email'  => 'martin.schophaus@jtl-software.com',
        ];

        $vs = new ValidationService([], [], []);
        $this->assertTrue($vs->validateSet($array, $rulesConfig)->isValid());

        $array['userId']  = 'hallo';
        $validationResult = $vs->validateSet($array, $rulesConfig);
        $this->assertFalse($validationResult->isValid());
        $this->assertTrue($validationResult->getFieldResult('email')->isValid());
        $this->assertFalse($validationResult->getFieldResult('userId')->isValid());
        $this->assertNull($validationResult->getSetAsArray());
        $this->assertNull($validationResult->getSetAsObject());

    }

    public function test_validateSet_keyDiffBetweenDefinitionAndSet_throwsException()
    {
        $keySet  = (new RuleSet())->integer()->gt(0);
        $mailSet = (new RuleSet())->email();

        $rulesConfig = [
            'userId' => $keySet,
            'email'  => $mailSet,
        ];
        $array       = [
            'userId'              => 10,
            'email'               => 'martin.schophaus@jtl-software.com',
            'something_malicious' => '<script>alert("evil things")</script>',
        ];
        $vs          = new ValidationService([], [], []);
        $this->expectException(\Exception::class);
        $vs->validateSet($array, $rulesConfig);
    }

    public function test_validateSet_nestedSet_throwsException()
    {
        $keySet  = (new RuleSet())->integer()->gt(0);
        $mailSet = (new RuleSet())->email();

        $rulesConfig = [
            'userId' => $keySet,
            'email'  => $mailSet,
        ];
        $array       = [
            'userId' => [10, 20],
            'email'  => 'martin.schophaus@jtl-software.com',
        ];
        $vs          = new ValidationService([], [], []);
        $this->expectException(\Exception::class);
        $vs->validateSet($array, $rulesConfig);
    }

    public function test_validateSet_worksWithObjects()
    {
        $keySet  = (new RuleSet())->integer()->gt(0);
        $mailSet = (new RuleSet())->email();

        $rulesConfig = [
            'userId' => $keySet,
            'email'  => $mailSet,
        ];
        $array       = [
            'userId' => 10,
            'email'  => 'martin.schophaus@jtl-software.com',
        ];
        $vs          = new ValidationService([], [], []);
        $result      = $vs->validateSet($array, $rulesConfig);
        $this->assertTrue($result->isValid());
    }

}

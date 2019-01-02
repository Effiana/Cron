<?php

/**
 * This file is part of the Cron package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Effiana\Cron;

use Effiana\Cron\Executor\Executor;
use Effiana\Cron\Resolver\ArrayResolver;
use Effiana\Cron\Job\ShellJob;

/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class CronTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Cron
     */
    private $cron;

    public function setUp()
    {
        $this->cron = new Cron();
    }

    public function tearDown()
    {
        unset($this->cron);
    }

    public function testExecutor()
    {
        $executor = new Executor();
        $this->cron->setExecutor($executor);

        $this->assertEquals($executor, $this->cron->getExecutor());
    }

    public function testResolver()
    {
        $resolver = new ArrayResolver();
        $this->cron->setResolver($resolver);

        $this->assertEquals($resolver, $this->cron->getResolver());
    }

    public function testRunReport()
    {
        $this->cron->setResolver(new ArrayResolver());
        $this->cron->setExecutor(new Executor());

        $this->assertInstanceOf('\Effiana\Cron\Report\ReportInterface', $this->cron->run());
    }

    public function testRunArray()
    {
        $task = new ShellJob();

        $this->cron->setResolver(new ArrayResolver([$task]));
        $this->cron->setExecutor(new Executor());

        $this->assertInstanceOf('\Effiana\Cron\Report\ReportInterface', $this->cron->run());
    }

    public function testExample()
    {
        $job = new \Effiana\Cron\Job\ShellJob();
        $job->setCommand('echo "total"');
        $job->setSchedule(new \Effiana\Cron\Schedule\CrontabSchedule('* * * * *'));

        $resolver = new \Effiana\Cron\Resolver\ArrayResolver();
        $resolver->addJob($job);

        $cron = new \Effiana\Cron\Cron();
        $cron->setExecutor(new \Effiana\Cron\Executor\Executor());
        $cron->setResolver($resolver);

        $report = $cron->run();

        $this->assertInstanceOf('\Effiana\Cron\Report\ReportInterface', $report);

        while ($cron->isRunning()) {
        }

        $reportOutput = $report->getReport($job)->getOutput();
        $this->assertEquals('total', trim($reportOutput[0]));
    }

    public function testNewExample()
    {
        $job1 = new \Effiana\Cron\Job\ShellJob();
        $job1->setCommand('ls -la');
        $job1->setSchedule(new \Effiana\Cron\Schedule\CrontabSchedule('*/5 * * * *'));

        $job2 = new \Effiana\Cron\Job\ShellJob();
        $job2->setCommand('ls -la');
        $job2->setSchedule(new \Effiana\Cron\Schedule\CrontabSchedule('0 0 * * 7'));

        $resolver = new \Effiana\Cron\Resolver\ArrayResolver();
        $resolver->addJob($job1);
        $resolver->addJob($job2);

        $cron = new \Effiana\Cron\Cron();
        $cron->setExecutor(new \Effiana\Cron\Executor\Executor());
        $cron->setResolver($resolver);

        $this->assertInstanceOf('\Effiana\Cron\Report\ReportInterface', $cron->run());
    }

    public function testDefaultExecutor()
    {
        $this->assertInstanceOf('\Effiana\Cron\Executor\Executor', $this->cron->getExecutor());
    }
}

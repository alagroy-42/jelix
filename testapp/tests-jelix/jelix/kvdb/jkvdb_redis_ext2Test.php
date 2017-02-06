<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @copyright   2010-2017 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jkvdb.lib.php');

/**
* Tests API jKVDb with the redis_ext driver, and configuration with prefixed keys
* @package     testapp
* @subpackage  jelix_tests module
*/

class jkvdb_redis_ext2Test extends jKVDbTest {

    protected $redis;

    function setUp () {
        $this->profile = 'usingredis_ext2';
        self::initJelixConfig();

        parent::setUp();
        if (!$this->_kvdbSetUp())
            return;

        $this->redis = new Redis();
        $this->redis->connect('localhost',6379);
        $this->redis->select(1);
        $this->redis->flushall();
    }

    public function tearDown() {
        if ($this->redis) {
            $this->redis->close();
        }
    }

    public function testGarbage (){

        $kv = jKVDb::getConnection($this->profile);

        $kv->set('remainingDataKey','remaining data');
        $kv->setWithTtl('garbage1DataKey','data send to the garbage',1);
        $kv->setWithTtl('garbage2DataKey','other data send to the garbage',strtotime("-1 day"));

        sleep(2);

        $this->assertTrue($kv->garbage());

        $this->assertEquals(serialize('remaining data'), $this->redis->get('bcd/remainingDataKey'));
        $this->assertFalse($this->redis->get('bcd/garbage1DataKey'));
        $this->assertFalse($this->redis->get('bcd/garbage2DataKey'));
    }

    public function testFlush (){

        $kv = jKVDb::getConnection($this->profile);

        $kv->set('flush1DataKey','some data',0);
        $kv->setWithTtl('flush2DataKey','data to remove',strtotime("+1 day"));
        $kv->setWithTtl('flush3DataKey','other data to remove',time()+30);

        $this->assertEquals(serialize('some data'), $this->redis->get('bcd/flush1DataKey'));
        $this->assertEquals(serialize('data to remove'), $this->redis->get('bcd/flush2DataKey'));
        $this->assertEquals(serialize('other data to remove'), $this->redis->get('bcd/flush3DataKey'));
        $this->assertTrue($kv->flush());
        $this->assertFalse($this->redis->get('bcd/flush1DataKey'));
        $this->assertFalse($this->redis->get('bcd/flush2DataKey'));
        $this->assertFalse($this->redis->get('bcd/flush3DataKey'));

    }
}


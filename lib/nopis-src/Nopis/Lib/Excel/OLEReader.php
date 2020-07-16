<?php

/**
 * A class for reading Microsoft Excel (97/2003) Spreadsheets.
 *
 * Version 2.21
 * Enhanced and maintained by Matt Kruse < http://mattkruse.com >
 *
 * DOCUMENTATION
 * =============
 *   http://code.google.com/p/php-excel-reader/wiki/Documentation
 *
 * Originally developed by Vadim Tkachenko under the name PHPExcelReader.
 * (http://sourceforge.net/projects/phpexcelreader)
 */

define('SPREADSHEET_EXCEL_READER_BIFF8', 0x600);
define('SPREADSHEET_EXCEL_READER_BIFF7', 0x500);
define('SPREADSHEET_EXCEL_READER_WORKBOOKGLOBALS', 0x5);
define('SPREADSHEET_EXCEL_READER_WORKSHEET', 0x10);
define('SPREADSHEET_EXCEL_READER_TYPE_BOF', 0x809);
define('SPREADSHEET_EXCEL_READER_TYPE_EOF', 0x0a);
define('SPREADSHEET_EXCEL_READER_TYPE_BOUNDSHEET', 0x85);
define('SPREADSHEET_EXCEL_READER_TYPE_DIMENSION', 0x200);
define('SPREADSHEET_EXCEL_READER_TYPE_ROW', 0x208);
define('SPREADSHEET_EXCEL_READER_TYPE_DBCELL', 0xd7);
define('SPREADSHEET_EXCEL_READER_TYPE_FILEPASS', 0x2f);
define('SPREADSHEET_EXCEL_READER_TYPE_NOTE', 0x1c);
define('SPREADSHEET_EXCEL_READER_TYPE_TXO', 0x1b6);
define('SPREADSHEET_EXCEL_READER_TYPE_RK', 0x7e);
define('SPREADSHEET_EXCEL_READER_TYPE_RK2', 0x27e);
define('SPREADSHEET_EXCEL_READER_TYPE_MULRK', 0xbd);
define('SPREADSHEET_EXCEL_READER_TYPE_MULBLANK', 0xbe);
define('SPREADSHEET_EXCEL_READER_TYPE_INDEX', 0x20b);
define('SPREADSHEET_EXCEL_READER_TYPE_SST', 0xfc);
define('SPREADSHEET_EXCEL_READER_TYPE_EXTSST', 0xff);
define('SPREADSHEET_EXCEL_READER_TYPE_CONTINUE', 0x3c);
define('SPREADSHEET_EXCEL_READER_TYPE_LABEL', 0x204);
define('SPREADSHEET_EXCEL_READER_TYPE_LABELSST', 0xfd);
define('SPREADSHEET_EXCEL_READER_TYPE_NUMBER', 0x203);
define('SPREADSHEET_EXCEL_READER_TYPE_NAME', 0x18);
define('SPREADSHEET_EXCEL_READER_TYPE_ARRAY', 0x221);
define('SPREADSHEET_EXCEL_READER_TYPE_STRING', 0x207);
define('SPREADSHEET_EXCEL_READER_TYPE_FORMULA', 0x406);
define('SPREADSHEET_EXCEL_READER_TYPE_FORMULA2', 0x6);
define('SPREADSHEET_EXCEL_READER_TYPE_FORMAT', 0x41e);
define('SPREADSHEET_EXCEL_READER_TYPE_XF', 0xe0);
define('SPREADSHEET_EXCEL_READER_TYPE_BOOLERR', 0x205);
define('SPREADSHEET_EXCEL_READER_TYPE_FONT', 0x0031);
define('SPREADSHEET_EXCEL_READER_TYPE_PALETTE', 0x0092);
define('SPREADSHEET_EXCEL_READER_TYPE_UNKNOWN', 0xffff);
define('SPREADSHEET_EXCEL_READER_TYPE_NINETEENFOUR', 0x22);
define('SPREADSHEET_EXCEL_READER_TYPE_MERGEDCELLS', 0xE5);
define('SPREADSHEET_EXCEL_READER_UTCOFFSETDAYS', 25569);
define('SPREADSHEET_EXCEL_READER_UTCOFFSETDAYS1904', 24107);
define('SPREADSHEET_EXCEL_READER_MSINADAY', 86400);
define('SPREADSHEET_EXCEL_READER_TYPE_HYPER', 0x01b8);
define('SPREADSHEET_EXCEL_READER_TYPE_COLINFO', 0x7d);
define('SPREADSHEET_EXCEL_READER_TYPE_DEFCOLWIDTH', 0x55);
define('SPREADSHEET_EXCEL_READER_TYPE_STANDARDWIDTH', 0x99);
define('SPREADSHEET_EXCEL_READER_DEF_NUM_FORMAT', "%s");


define('NUM_BIG_BLOCK_DEPOT_BLOCKS_POS', 0x2c);
define('SMALL_BLOCK_DEPOT_BLOCK_POS', 0x3c);
define('ROOT_START_BLOCK_POS', 0x30);
define('BIG_BLOCK_SIZE', 0x200);
define('SMALL_BLOCK_SIZE', 0x40);
define('EXTENSION_BLOCK_POS', 0x44);
define('NUM_EXTENSION_BLOCK_POS', 0x48);
define('PROPERTY_STORAGE_BLOCK_SIZE', 0x80);
define('BIG_BLOCK_DEPOT_BLOCKS_POS', 0x4c);
define('SMALL_BLOCK_THRESHOLD', 0x1000);
// property storage offsets
define('SIZE_OF_NAME_POS', 0x40);
define('TYPE_POS', 0x42);
define('START_BLOCK_POS', 0x74);
define('SIZE_POS', 0x78);
define('IDENTIFIER_OLE', pack("CCCCCCCC", 0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1));

namespace Nopis\Lib\Excel;

class OLEReader
{

    public static $_error = null;
    private static $data = '';
    private static $numBigBlockDepotBlocks;
    private static $sbdStartBlock;
    private static $rootStartBlock;
    private static $extensionBlock;
    private static $numExtensionBlocks;
    private static $bigBlockChain = array();
    private static $smallBlockChain = array();
    private static $entry;
    private static $props = array();
    private static $rootentry;
    private static $wrkbook;

    public static function read($sFileName)
    {
        // check if file exist and is readable (Darko Miljanovic)
        if (!is_readable($sFileName)) {
            self::$_error = 1;
            return false;
        }
        self::$data = @file_get_contents($sFileName);
        if (!self::$data) {
            self::$_error = 1;
            return false;
        }
        if (substr(self::$data, 0, 8) != IDENTIFIER_OLE) {
            self::$_error = 1;
            return false;
        }
        self::$numBigBlockDepotBlocks = self::_GetInt4d(self::$data, NUM_BIG_BLOCK_DEPOT_BLOCKS_POS);
        self::$sbdStartBlock = self::_GetInt4d(self::$data, SMALL_BLOCK_DEPOT_BLOCK_POS);
        self::$rootStartBlock = self::_GetInt4d(self::$data, ROOT_START_BLOCK_POS);
        self::$extensionBlock = self::_GetInt4d(self::$data, EXTENSION_BLOCK_POS);
        self::$numExtensionBlocks = self::_GetInt4d(self::$data, NUM_EXTENSION_BLOCK_POS);

        $bigBlockDepotBlocks = array();
        $pos = BIG_BLOCK_DEPOT_BLOCKS_POS;
        $bbdBlocks = self::$numBigBlockDepotBlocks;
        if (self::$numExtensionBlocks != 0) {
            $bbdBlocks = (BIG_BLOCK_SIZE - BIG_BLOCK_DEPOT_BLOCKS_POS) / 4;
        }

        for ($i = 0; $i < $bbdBlocks; $i++) {
            $bigBlockDepotBlocks[$i] = self::_GetInt4d(self::$data, $pos);
            $pos += 4;
        }


        for ($j = 0; $j < self::$numExtensionBlocks; $j++) {
            $pos = (self::$extensionBlock + 1) * BIG_BLOCK_SIZE;
            $blocksToRead = min(self::$numBigBlockDepotBlocks - $bbdBlocks, BIG_BLOCK_SIZE / 4 - 1);

            for ($i = $bbdBlocks; $i < $bbdBlocks + $blocksToRead; $i++) {
                $bigBlockDepotBlocks[$i] = self::_GetInt4d(self::$data, $pos);
                $pos += 4;
            }

            $bbdBlocks += $blocksToRead;
            if ($bbdBlocks < self::$numBigBlockDepotBlocks) {
                self::$extensionBlock = self::_GetInt4d(self::$data, $pos);
            }
        }

        // readBigBlockDepot
        $pos = 0;
        $index = 0;
        self::$bigBlockChain = array();

        for ($i = 0; $i < self::$numBigBlockDepotBlocks; $i++) {
            $pos = ($bigBlockDepotBlocks[$i] + 1) * BIG_BLOCK_SIZE;
            //echo "pos = $pos";
            for ($j = 0; $j < BIG_BLOCK_SIZE / 4; $j++) {
                self::$bigBlockChain[$index] = self::_GetInt4d(self::$data, $pos);
                $pos += 4;
                $index++;
            }
        }

        // readSmallBlockDepot();
        $pos = 0;
        $index = 0;
        $sbdBlock = self::$sbdStartBlock;
        self::$smallBlockChain = array();

        while ($sbdBlock != -2) {
            $pos = ($sbdBlock + 1) * BIG_BLOCK_SIZE;
            for ($j = 0; $j < BIG_BLOCK_SIZE / 4; $j++) {
                self::$smallBlockChain[$index] = self::_GetInt4d(self::$data, $pos);
                $pos += 4;
                $index++;
            }
            $sbdBlock = self::$bigBlockChain[$sbdBlock];
        }


        // readData(rootStartBlock)
        $block = self::$rootStartBlock;
        $pos = 0;
        self::$entry = self::__readData($block);
        self::__readPropertySets();
    }

    private static function __readData($bl)
    {
        $block = $bl;
        $pos = 0;
        $data = '';
        while ($block != -2) {
            $pos = ($block + 1) * BIG_BLOCK_SIZE;
            $data = $data . substr(self::$data, $pos, BIG_BLOCK_SIZE);
            $block = self::$bigBlockChain[$block];
        }
        return $data;
    }

    private static function __readPropertySets()
    {
        $offset = 0;
        while ($offset < strlen(self::$entry)) {
            $d = substr(self::$entry, $offset, PROPERTY_STORAGE_BLOCK_SIZE);
            $nameSize = ord($d[SIZE_OF_NAME_POS]) | (ord($d[SIZE_OF_NAME_POS + 1]) << 8);
            $type = ord($d[TYPE_POS]);
            $startBlock = self::_GetInt4d($d, START_BLOCK_POS);
            $size = self::_GetInt4d($d, SIZE_POS);
            $name = '';
            for ($i = 0; $i < $nameSize; $i++) {
                $name .= $d[$i];
            }
            $name = str_replace("\x00", "", $name);
            self::$props[] = array(
                'name' => $name,
                'type' => $type,
                'startBlock' => $startBlock,
                'size' => $size);
            if ((strtolower($name) == "workbook") || ( strtolower($name) == "book")) {
                self::$wrkbook = count(self::$props) - 1;
            }
            if ($name == "Root Entry") {
                self::$rootentry = count(self::$props) - 1;
            }
            $offset += PROPERTY_STORAGE_BLOCK_SIZE;
        }
    }

    public static function getWorkBook()
    {
        if (self::$props[self::$wrkbook]['size'] < SMALL_BLOCK_THRESHOLD) {
            $rootdata = self::$__readData(self::$props[self::$rootentry]['startBlock']);
            $streamData = '';
            $block = self::$props[self::$wrkbook]['startBlock'];
            $pos = 0;
            while ($block != -2) {
                $pos = $block * SMALL_BLOCK_SIZE;
                $streamData .= substr($rootdata, $pos, SMALL_BLOCK_SIZE);
                $block = self::$smallBlockChain[$block];
            }
            return $streamData;
        } else {
            $numBlocks = self::$props[self::$wrkbook]['size'] / BIG_BLOCK_SIZE;
            if (self::$props[self::$wrkbook]['size'] % BIG_BLOCK_SIZE != 0) {
                $numBlocks++;
            }

            if ($numBlocks == 0)
                return '';
            $streamData = '';
            $block = self::$props[self::$wrkbook]['startBlock'];
            $pos = 0;
            while ($block != -2) {
                $pos = ($block + 1) * BIG_BLOCK_SIZE;
                $streamData .= substr(self::$data, $pos, BIG_BLOCK_SIZE);
                $block = self::$bigBlockChain[$block];
            }
            return $streamData;
        }
    }

    public static function _GetInt4d($data, $pos)
    {
        $value = ord($data[$pos]) | (ord($data[$pos + 1]) << 8) | (ord($data[$pos + 2]) << 16) | (ord($data[$pos + 3]) << 24);
        if ($value >= 4294967294) {
            $value = -2;
        }
        return $value;
    }

}
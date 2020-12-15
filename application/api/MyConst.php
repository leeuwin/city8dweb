<?php
/**
 * Created by PhpStorm.
 * User: think
 * Date: 2020/9/17
 * Time: 14:22
 */
namespace app\api;

class MyConst
{
    const MaNum = 49;
    const MaSmall = 24;
    //board
    const PingMa1 = 0;
    const PingMa2 = 1;
    const PingMa3 = 2;
    const PingMa4 = 3;
    const PingMa5 = 4;
    const PingMa6 = 5;
    const TeMa = 6;
    //生肖
    const SX_SHU = 1;
    const SX_NIU = 2;
    const SX_HU = 3;
    const SX_TU = 4;
    const SX_LONG = 5;
    const SX_SHE = 6;
    const SX_MA = 7;
    const SX_YANG = 8;
    const SX_HOU = 9;
    const SX_JI = 10;
    const SX_GOU = 11;
    const SX_ZHU = 12;

    //今年生肖数字
    const THIS_YEAR = self::SX_SHU;
    const SX_GROUP = array(
        1=>array(1,13,25,37,49),
        2=>array(12,24,36,48),
        3=>array(11,23,35,47),
        4=>array(10,22,34,46),
        5=>array(9,21,33,45),
        6=>array(8,20,32,44),
        7=>array(7,19,31,43),
        8=>array(6,18,30,42),
        9=>array(5,17,29,41),
        10=>array(4,16,28,40),
        11=>array(3,15,27,39),
        12=>array(2,14,26,38)
    );

    const HEAD_GROUP = array(
        0=>array(1,2,3,4,5,6,7,8,9),
        1=>array(10,11,12,13,14,15,16,17,18,19),
        2=>array(20,21,22,23,24,25,26,27,28,29),
        3=>array(30,31,32,33,34,35,36,37,38,39),
        4=>array(40,41,42,43,44,45,46,47,48,49));
    const TAIL_GROUP = array(
        0=>array(10,20,30,40),
        1=>array(1,11,21,31,41),
        2=>array(2,12,22,32,42),
        3=>array(3,13,23,33,43),
        4=>array(4,14,24,34,44),
        5=>array(5,15,25,35,45),
        6=>array(6,16,26,36,46),
        7=>array(7,17,27,37,47),
        8=>array(8,18,28,38,48),
        9=>array(9,19,29,39,49)
    );

    const FIVE_ELEMENT_GROUP = array(
        1=>array(1,6,11,16,21,26,31,36,41,46),
        2=>array(2,7,12,17,22,27,32,37,42,47),
        3=>array(3,8,13,18,23,28,33,38,43,48),
        4=>array(4,9,14,19,24,29,34,39,44,49),
        5=>array(5,10,15,20,25,30,35,40,45)
    );

    //前后
    const GROUP_SXS_FRONT = array(self::SX_SHU,self::SX_NIU,self::SX_HU,self::SX_TU,self::SX_LONG,self::SX_SHE);
    const GROUP_SXS_BACK = array(self::SX_MA,self::SX_YANG,self::SX_HOU,self::SX_JI,self::SX_GOU,self::SX_ZHU);
    //天地
    const GROUP_SXS_UPPER = array(self::SX_NIU,self::SX_TU,self::SX_LONG,self::SX_MA,self::SX_HOU,self::SX_ZHU);
    const GROUP_SXS_DOWN = array(self::SX_SHU,self::SX_HU,self::SX_SHE,self::SX_YANG,self::SX_JI,self::SX_GOU);
    //家野
    const GROUP_SXS_HOME = array(self::SX_NIU,self::SX_MA,self::SX_YANG,self::SX_JI,self::SX_GOU,self::SX_ZHU);
    const GROUP_SXS_WILD = array(self::SX_SHU,self::SX_HU,self::SX_TU,self::SX_LONG,self::SX_SHE,self::SX_HOU);

    //color
    const COLOR_RED = 1;
    const COLOR_BLUE = 2;
    const COLOR_GREEN = 3;

    //组合定义
    const GROUP_DX_X = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24);
    const GROUP_DX_D = array(25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48);
    const GROUP_DS_D = array(1,3,5,7,9,11,13,15,17,19,21,23,25,27,29,31,33,35,37,39,41,43,45,47);
    const GROUP_DS_S = array(2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48);
    const GROUP_HDX_D = array(7,8,9,16,17,18,19,25,26,27,28,29,34,35,36,37,38,39,43,44,45,46,47,48);
    const GROUP_HDX_X = array(1,2,3,4,5,6,10,11,12,13,14,15,20,21,22,23,24,30,31,32,33,40,41,42);
    const GROUP_HDS_D = array(1,3,5,7,9,10,12,14,16,18,21,23,25,27,29,30,32,34,36,38,41,43,45,47);
    const GROUP_HDS_S = array(2,4,6,8,11,13,15,17,19,20,22,24,26,28,31,33,35,37,39,40,42,44,46,48);
    const GROUP_WDX_D = array(5,6,7,8,9,15,16,17,18,19,25,26,27,28,29,35,36,37,38,39,45,46,47,48);
    const GROUP_WDX_X = array(1,2,3,4,10,11,12,13,14,20,21,22,23,24,30,31,32,33,34,40,41,42,43,44);
    const GROUP_SB_RED = array(1,2,7,8,12,13,18,19,23,24,29,30,34,35,40,45,46);
    const GROUP_SB_BLUE = array(3,4,9,10,14,15,20,25,26,31,36,37,41,42,47,48);
    const GROUP_SB_GREEN = array(5,6,11,16,17,21,22,27,28,32,33,38,39,43,44,49);


    //半,49为平
    const GROUP_RED_DA = array(29,30,34,35,40,45,46);//1
    const GROUP_RED_XIAO = array(1,2,7,8,12,13,18,19,23,24);//2
    const GROUP_RED_DAN = array(1,7,13,19,23,29,35,45);//3
    const GROUP_RED_SHUANG = array(2,8,12,18,24,30,34,40,46);//4
    const GROUP_BLUE_DA = array(25,26,31,36,37,41,42,47,48);//5
    const GROUP_BLUE_XIAO = array(3,4,9,10,14,15,20);//6
    const GROUP_BLUE_DAN = array(3,9,15,25,31,37,41,47);//7
    const GROUP_BLUE_SHUANG = array(4,10,14,20,26,36,42,48);//8
    const GROUP_GREEN_DA = array(27,28,32,33,38,39,43,44);//9
    const GROUP_GREEN_XIAO = array(5,6,11,16,17,21,22);//10
    const GROUP_GREEN_DAN = array(5,11,17,21,27,33,39,43);//11
    const GROUP_GREEN_SHUANG = array(6,16,22,28,32,38,44);//12
    //半半，49为平
    const GROUP_RED_DA_DAN = array(29,35,45);
    const GROUP_RED_DA_SHUANG = array(30,34,40,46);
    const GROUP_RED_XIAO_DAN = array(1,7,13,19,23);
    const GROUP_RED_XIAO_SHUANG = array(2,8,12,18,24);
    const GROUP_BLUE_DA_DAN = array(25,31,37,41,47);
    const GROUP_BLUE_DA_SHUANG = array(26,36,42,48);
    const GROUP_BLUE_XIAO_DAN = array(3,9,15);
    const GROUP_BLUE_XIAO_SHUANG = array(4,10,14,20);
    const GROUP_GREEN_DA_DAN = array(27,33,39,43);
    const GROUP_GREEN_DA_SHUANG = array(28,32,38,44);
    const GROUP_GREEN_XIAO_DAN = array(5,11,17,21);
    const GROUP_GREEN_XIAO_SHUANG = array(6,16,22);

    //type类型定义
    const S_MA_BOARD = 0;

    //liangmian
    const S_LM_DX = 10;     //大小
    const S_LM_DS = 11;     //单双
    const S_LM_HDX = 12;    //合大小
    const S_LM_HDS = 13;    //合单双
    const S_LM_WDX = 14;    //尾大小
    const S_LM_TDX = 15;    //天地肖
    const S_LM_QHX = 16;    //前后肖
    const S_LM_JYX = 17;    //家野肖

    const G_LM_ZDX = 18;    //总大小
    const G_LM_ZDS = 19;    //总单双

    const S_TM = 20;        //tm
    const G_ZM = 21;        //zm
    const S_ZMT = 22;       //zmt

    const S_ZM16_DX = 23;   //zm16大小
    const S_ZM16_DS = 24;   //zm16单双
    const S_ZM16_HDX = 25;   //zm16合大小
    const S_ZM16_HDS = 26;   //zm16合单双
    const S_ZM16_WDX = 27;   //zm16尾大小
    const S_ZM16_SB = 28;   //zm16色波
    const G_ZMGG = 29;   //zm过关
    //lianma
    const G_LM_4 = 30;   //lm4/4
    const G_LM_3 = 31;      //lm3/3
    const G_LM_3_2 = 32;    //lm3/3+2/3
    const G_LM_2 = 33;      //lm2/2
    const G_LM_2_T = 34;     //lm2+中特
    const G_LM_T = 35;       //lm中特

    const G_LX_2 = 40;      //2连X
    const G_LX_3 = 41;      //3连X
    const G_LX_4 = 42;      //4连X
    const G_LX_5 = 43;      //5连X

    const G_LW_2 = 50;      //2连W
    const G_LW_3 = 51;      //3连W
    const G_LW_4 = 52;      //4连W
    const G_LW_5 = 53;      //5连W

    const G_NO_5 = 60;      //5不中
    const G_NO_6 = 61;      //6不中
    const G_NO_7 = 62;      //7不中
    const G_NO_8 = 63;      //8不中
    const G_NO_9 = 64;      //9不中
    const G_NO_10 = 65;      //10不中
    const G_NO_11 = 66;      //11不中
    const G_NO_12 = 67;      //12不中

    const S_SX_TX = 70;     //sx特x
    const G_SX_ZEX = 71;    //sx正x
    const G_SX_YX = 72;     //sx一x
    const G_SX_ZOX = 73;    //sx总x

    const S_HX = 74;        //合x

    const G_SB_7SB = 80;        //7sebo
    const S_SB_3SB = 81;        //3sebo
    const S_SB_BB = 82;         //半bo
    const S_SB_BBB = 83;        //半半bo

    const S_WS_TS = 90;
    const S_WS_WS = 91;
    const G_WS_ZTWS = 92;

    const S_5X = 100;

    const G_7M_DS = 110;
    const G_7M_DX = 111;

    const G_Z1_5 = 120;
    const G_Z1_6 = 121;
    const G_Z1_7 = 122;
    const G_Z1_8 = 123;
    const G_Z1_9 = 124;
    const G_Z1_10 = 125;

    //tm中将倍率
    const Multiple = 48.0;
    //中将倍率 (浮点数)
    const TypeMultiple = array(
        self::S_LM_DX=>1.98,
        self::S_LM_DS=>1.98,
        self::S_LM_HDS=>1.98,
        self::S_LM_HDX=>1.98,
        self::S_LM_WDX=>1.98,
        self::S_LM_TDX=>array(1.97,1.891),//天and地
        self::S_LM_QHX=>array(1.97,1.97),
        self::S_LM_JYX=>array(1.97,1.901),//家and野
        self::G_LM_ZDS=>1.98,
        self::G_LM_ZDX=>1.98,
        self::S_TM=>48.0,
        self::G_ZM=>8.02,
        self::S_ZMT=>46.999,
        self::S_ZM16_DX=>1.98,
        self::S_ZM16_DS=>1.98,
        self::S_ZM16_HDX=>1.98,
        self::S_ZM16_HDS=>1.98,
        self::S_ZM16_WDX=>1.98,
        self::S_ZM16_SB=>array(2.78,2.859,2.859),
        self::G_ZMGG=>array(1.969,1.969,1.969,1.969,1.969,1.969,1.969,1.969,1.969,1.969,2.7,2.849,2.849),//单/双/大/小/h单/j双/h大/h小/w大/w小/r/g/b
        self::G_LM_4=>10000.0,
        self::G_LM_3=>649.9,
        self::G_LM_2=>70.0,
        self::G_LM_3_2=>array(100.0,23.0),//前者33，后者32
        self::G_LM_2_T=>array(51.0,31.0),//前者22，后者2t
        self::G_LM_T=>154.99,
        self::G_LX_2=>array(4.119,3.32),//后者当年xiao
        self::G_LX_3=>array(11.12,9.02),//后者当年xiao
        self::G_LX_4=>array(32.0,28.0),//后者当年xiao
        self::G_LX_5=>array(97.99,87.0),//后者当年xiao
        self::G_LW_2=>3.18,
        self::G_LW_3=>7.08,
        self::G_LW_4=>15.8,
        self::G_LW_5=>40.0,
        self::G_NO_5=>2.1,
        self::G_NO_6=>2.5,
        self::G_NO_7=>3.0,
        self::G_NO_8=>3.6,
        self::G_NO_9=>4.3,
        self::G_NO_10=>5.199,
        self::G_NO_11=>6.35,
        self::G_NO_12=>7.799,
        self::S_SX_TX=>array(11.6,9.5),//后者当年肖
        self::G_SX_ZEX=>array(1.92,1.75),//后者当年肖
        self::G_SX_YX=>array(2.1,1.8),//后者为当年xiao
        self::G_SX_ZOX=>array(14.999,3.07,1.959,5.399,1.98,1.849),//234xiao,5xiao,6xiao,7xiao,odd,even
        self::S_HX=>47,
        self::G_SB_7SB=>array(24.999,2.649,3.0,3.0),//draw,red,blue,green
        self::S_SB_3SB=>array(2.78,2.859,2.859),//red,blue,green
        self::S_SB_BB=>array(6.499,4.5,5.58,5.06,5.58,6.519,5.58,6.45,5.0,6.58,5.58,5.58),
        self::S_SB_BBB=>array(14.8,11.12,8.92,8.92,11.12,11.12,11.12,14.819,8.92,11.12,14.819,11.12),
        self::S_WS_TS=>array(5.0,4.36),
        self::S_WS_WS=>array(11.159,9.25),
        self::G_WS_ZTWS=>array(1.8, 2.1),
        self::S_5X=>array(4.7,4.042,4.7,4.7,4.8),
        self::G_7M_DS=>array(231.3,24.109,6.45,3.4,3.2,5.56,19.199,169.32),//单:双分别对应 0:7,1:6,2:5,3:4,4:3,5:2,6:1,7:0
        self::G_7M_DX=>array(231.3,24.109,6.45,3.4,3.2,5.56,19.199,169.32),//大:小分别对应 0:7,1:6,2:5,3:4,4:3,5:2,6:1,7:0
        self::G_Z1_5=>2.349,
        self::G_Z1_6=>2.28,
        self::G_Z1_7=>2.269,
        self::G_Z1_8=>2.32,
        self::G_Z1_9=>2.41,
        self::G_Z1_10=>2.51,
    );
}
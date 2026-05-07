#include <SoftwareSerial.h>
#include <SHT1x.h>
#include <Wire.h>
#include <BMP085.h>

#define ESP Serial1

String devUniq = "00000003";
String devType = "meteo1";
String homePassword = "...";
String homeServer = "192.168.1.1";

SHT1x sht1x(10, 11);
SoftwareSerial mhz19(8, 9);
BMP085 dps = BMP085();

float myTemp = 0, oldTemp = 0;
float myHumd = 0, oldHumd = 0;
int myCO2 = 0, oldCO2 = 0;
long myPress = 0, oldPress = 0;

void setup() {
  Serial.begin(115200);
  ESP.begin(115200);
  while (!ESP);
  mhz19.begin(9600);
  while (!mhz19);
  Wire.begin();
  delay(1000);
  dps.init();
  delay(5000);
  ESP.println("AT+CIPMUX=1");
  delay(100);
  ESP.println("AT+CIPSERVER=1,777");
  ESP.println("AT+CIPSTO=10");
}

void process(String cmd, String prm) {
  Serial.println(cmd);
  Serial.println(prm);
}

long myTime1 = 0;
long myTime2 = 0;
long myTimeCO2 = 0;
byte co2_cmd[9] = {0xFF,0x01,0x86,0x00,0x00,0x00,0x00,0x00,0x79};
void send_data() {
  String data = "";
  data = "^#" + devUniq + "#" + homePassword + "#update#" + String(myTemp, 1) + "," + String(myHumd, 0) + "," + String(myCO2) + "," + String(myPress) + "#$";
  Serial.println("data = " + data);
  int len = data.length();
  ESP.println("AT+CIPSTART=2,\"TCP\",\"" + homeServer + "\",7777");
  if (ESP.find("Error")) {
    Serial.println("connection error");
    return;
  }
  ESP.print("AT+CIPSEND=2,");
  ESP.println(len);
  delay(10);
  if (!ESP.find(">")) {
    ESP.println("AT+CIPCLOSE=2");
    Serial.println("timeout error");
    return;
  }
  ESP.print(data);
  ESP.println("AT+CIPCLOSE=2");
}

String pack = "";
void loop() {
  String id, pw, cmd, prm;
  int i, l, q;
  
  if (Serial.available()) {
    ESP.write(Serial.read());
  }
  
  if (ESP.available()) {
    char c = ESP.read();
    Serial.write(c);
    switch (c) {
      case '+':
        pack = "";
      break;
      case '^':
        pack = "";
      break;
      case '$':
        id = "";
        pw = "";
        cmd = "";
        prm = "";
        i = 0, q = 0;
        l = pack.length();
        if (!l) break;
        while (i < l) {
          c = pack[i++];
          if (c == '#') {
            q++;
            continue;
          }
          switch (q) {
            case 1: id += c; break;
            case 2: pw += c; break;
            case 3: cmd += c; break;
            case 4: prm += c; break;
          }
        }
        if (id != devUniq) break;
        if (pw != homePassword) break;
        process(cmd, prm);
      break;
      default:
        pack += c;
      break;
    }
  }

  byte crc;
  unsigned char co2_res[9];
  if (millis() - myTime1 > 2000) {
    oldTemp = myTemp;
    myTemp = sht1x.readTemperatureC();
    myTemp = round(myTemp * 10) / 10.0;
    oldHumd = myHumd;
    myHumd = sht1x.readHumidity();
    myHumd = round(myHumd);
    oldCO2 = myCO2;
    if (millis() - myTimeCO2 > 10000) {
        mhz19.write(co2_cmd, 9);
        memset(co2_res, 0, 9);
        mhz19.readBytes(co2_res, 9);
        crc = 0;
        for (int k = 1; k < 8; k++) crc += co2_res[k];
        crc = 255 - crc;
        crc++;
        if (co2_res[0] == 0xFF && co2_res[1] == 0x86 && co2_res[8] == crc) {
          unsigned int resHigh = (unsigned int)co2_res[2];
          unsigned int resLow = (unsigned int)co2_res[3];
          int co2 = (256 * resHigh) + resLow;
          if (co2) {
            myCO2 = (co2 / 10) * 10;
          }
        }
        myTimeCO2 = millis();
    }
    oldPress = myPress;
    dps.getPressure(&myPress);
    myPress = myPress / 133.33;
    if (myTemp != oldTemp || myHumd != oldHumd || myCO2 != oldCO2 || myPress != oldPress || millis() - myTime2 > 60000) {
      send_data();
      myTime2 = millis();
    }
    myTime1 = millis();
  }
}

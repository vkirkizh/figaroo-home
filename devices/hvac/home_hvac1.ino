#include "IRremote2.h"
// http://arcfn.com/2009/08/multi-protocol-infrared-remote-library.html

#include <LiquidCrystal.h>
#include "DHT.h"

#define ESP Serial1

String devUniq = "00000002";
String devType = "hvac1";
String homePassword = "...";
String homeServer = "192.168.1.1";

DHT dht(2, DHT22);

LiquidCrystal lcd(9, 8, 7, 6, 5, 4);

boolean myStatus = true;
int myTemp = 24;
float curTemp = 0, oldTemp = 0;

IRsend irsend;

void setup() {
  Serial.begin(115200);
  ESP.begin(115200);
  while (!ESP);
  delay(5000);
  ESP.println("AT+CIPMUX=1");
  delay(100);
  ESP.println("AT+CIPSERVER=1,777");
  ESP.println("AT+CIPSTO=10");
  lcd.begin(16, 2);
  lcd.command(0b101010);
  lcd.setCursor(0, 0);
  lcd.print("Mode ON ");
  lcd.setCursor(0, 1);
  lcd.print("Temp 24\xB0""C 0.0\xB0""C ");
  dht.begin();
}

void process(String cmd, String prm) {
  Serial.println(cmd);
  Serial.println(prm);
  if (cmd == "status") {
    if (prm == "on") myStatus = true;
    if (prm == "off") myStatus = false;
    irsend.sendHvacToshiba(HVAC_COLD, myTemp, FAN_SPEED_AUTO, !myStatus);
    if (myStatus) {
      lcd.setCursor(0, 0);
      lcd.print("Mode: ON ");
    } else {
      lcd.setCursor(0, 0);
      lcd.print("Mode: OFF");
    }
  }
  if (cmd == "temp") {
    myTemp = prm.toInt();
    irsend.sendHvacToshiba(HVAC_COLD, myTemp, FAN_SPEED_AUTO, !myStatus);
    lcd.setCursor(15, 1);
    lcd.print(" ");
    lcd.setCursor(0, 1);
    lcd.print("Temp " + String(myTemp) + "\xB0""C " + String(curTemp, 1) + "\xB0""C");
  }
}

long myTime1 = 0;
long myTime2 = 0;
void send_data() {
  String data = "";
  data = "^#" + devUniq + "#" + homePassword + "#update#" + String(curTemp, 1) + "#$";
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

  if (millis() - myTime1 > 2000) {
    oldTemp = curTemp;
    curTemp = dht.readTemperature();
    if (isnan(curTemp)) return;
    curTemp = round(curTemp * 10) / 10.0;
    if (curTemp != oldTemp || millis() - myTime2 > 60000) {
      send_data();
      lcd.setCursor(15, 1);
      lcd.print(" ");
      lcd.setCursor(0, 1);
      lcd.print("Temp " + String(myTemp) + "\xB0""C " + String(curTemp, 1) + "\xB0""C");
      myTime2 = millis();
    }
    myTime1 = millis();
  }
}

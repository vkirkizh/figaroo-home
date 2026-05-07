#define ESP Serial1

String devUniq = "00000001";
String devType = "switch1";
String homePassword = "...";
String homeServer = "192.168.1.1";

#define PIN_REL 11 // реле
#define PIN_LED 5  // светодиод
#define PIN_BTN 8  // кнопка

boolean myStatus = false;

void setup() {

  Serial.begin(115200);
  ESP.begin(115200);
  while (!ESP);
  delay(5000);

  ESP.println("AT+CIPMUX=1");
  delay(100);
  ESP.println("AT+CIPSERVER=1,777");
  ESP.println("AT+CIPSTO=10");

  pinMode(PIN_LED, OUTPUT);
  digitalWrite(PIN_LED, myStatus);

  pinMode(PIN_REL, OUTPUT);
  digitalWrite(PIN_REL, myStatus);

  pinMode(PIN_BTN, INPUT);
  
}

void process(String cmd, String prm) {
  if (cmd == "status") {
    if (prm == "on") myStatus = true;
    if (prm == "off") myStatus = false;
    digitalWrite(PIN_LED, myStatus);
    digitalWrite(PIN_REL, myStatus);
  }
}

boolean btn_flag = false;
long btn_time = 0;
void btn_pressed() {
  myStatus = !myStatus;
  digitalWrite(PIN_LED, myStatus);
  digitalWrite(PIN_REL, myStatus);

  String data = "";
  data = "^#" + devUniq + "#" + homePassword + "#status#" + (myStatus ? "on" : "off") + "#$";
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

  int pressed = !digitalRead(PIN_BTN);
  if (pressed && !btn_flag) {
    btn_flag = true;
    btn_time = millis();
    btn_pressed();
  }
  if (millis() - btn_time > 200 && !pressed) {
    btn_flag = false;
  }
}

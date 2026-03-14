/**
 * W.I.Y LAUNDRY SHOP - BIOMETRIC FINGERPRINT SCANNER
 * ESP8266 + R307 Fingerprint Sensor Integration
 * 
 * Features:
 * - Connect R307 fingerprint sensor to ESP8266 via serial
 * - Scan fingerprints and send to PHP API for time-in/time-out
 * - WiFi connectivity for API communication
 * - LED feedback for scan status
 * - Serial console for debugging
 * 
 * Hardware:
 * - ESP8266 NodeMCU
 * - R307 Fingerprint Sensor (57600 baud)
 * - 5V Power supply
 * - Voltage divider (2x 1k resistor)
 * 
 * Libraries Required:
 * - Adafruit Fingerprint Sensor Library
 * - ArduinoJson
 * - ESP8266WiFi
 * - ESP8266HTTPClient
 * 
 * Installation:
 * 1. Install Arduino IDE
 * 2. Add ESP8266 board: Tools > Board Manager > Search "esp8266" > Install
 * 3. Install libraries: Sketch > Include Library > Manage Libraries
 * 4. Update WiFi credentials below
 * 5. Update server URL with your domain/IP
 * 6. Upload to ESP8266
 */

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <Adafruit_Fingerprint.h>
#include <SoftwareSerial.h>
#include <ArduinoJson.h>



// WiFi Configuration PLEASE CHANGE! ACCORDING TO THE WIFI YOU WANT TO CONNECT TO
const char* WIFI_SSID = "Kaedehara 2g";
const char* WIFI_PASSWORD = "kissmuna";

// Server Configuration PLEASE CHANGE! ACCORDING TO THE WIFI YOU WANT TO CONNECT TO
const char* SERVER_HOST = "http://192.168.1.167"; // Change to your server IP/domain
const int SERVER_PORT = 80;
const char* SERVER_BASE_PATH = "/siaboys/siaboyz";
const char* API_TIMEIN_ENDPOINT = "/api/biometric_timein.php";
const char* API_ENROLL_ENDPOINT = "/api/biometric_enroll.php";

// Fingerprint Sensor Pins
#define RX_PIN D5  // GPIO14 - R307 TX pin
#define TX_PIN D6  // GPIO12 - R307 RX pin (with voltage divider)

// LED Pins (yellow on D1, green on D2)
#define LED_OK_PIN D1  // GPIO5
#define LED_ERR_PIN D2 // GPIO4
// Set true if your LEDs are wired active-low (LED on when pin is LOW)
#define LED_ACTIVE_LOW false
// Enable LED test pattern on boot
#define LED_TEST_MODE true
#define LED_TEST_HOLD_MS 2000


SoftwareSerial fingerSerial(RX_PIN, TX_PIN);
Adafruit_Fingerprint finger = Adafruit_Fingerprint(&fingerSerial);
WiFiClient wifiClient;

int scanMode = 0; // 0 = Time-in/out, 1 = Enrollment mode
int enrollingStaffId = 0;

// ============================================================
// FUNCTION PROTOTYPES

void setLed(int pin, bool on);
void blink(int pin, int times, int delayMs);
void runLedTest();
void scanFingerprint();
void enrollFingerprint();
void sendTimeInRequest(int fingerprintId);
void sendEnrollmentToServer(int staffId, int fingerprintId);
void handleSerialInput();

// ============================================================
// SETUP
// ============================================================
void setup() {
  Serial.begin(115200);
  delay(1000);
  
  // Initialize LED pins
  pinMode(LED_OK_PIN, OUTPUT);
  pinMode(LED_ERR_PIN, OUTPUT);
  setLed(LED_OK_PIN, false);
  setLed(LED_ERR_PIN, false);

  if (LED_TEST_MODE) {
    runLedTest();
  }
  
  Serial.println("\n\n");
  Serial.println("╔════════════════════════════════════════╗");
  Serial.println("║  W.I.Y LAUNDRY - BIOMETRIC SCANNER    ║");
  Serial.println("║  ESP8266 + R307 Fingerprint Module    ║");
  Serial.println("╚════════════════════════════════════════╝\n");
  
  // Display configuration
  Serial.println("[CONFIG] System Configuration:");
  Serial.print("  WiFi SSID: ");
  Serial.println(WIFI_SSID);
  Serial.print("  WiFi Password: ");
  Serial.println(WIFI_PASSWORD);
  Serial.print("  Server Host: ");
  Serial.println(SERVER_HOST);
  Serial.print("  API Endpoints:");
  Serial.println("");
  Serial.print("    - Time-in: ");
  Serial.println(API_TIMEIN_ENDPOINT);
  Serial.print("    - Enroll: ");
  Serial.println(API_ENROLL_ENDPOINT);
  Serial.println("");
  
  // Initialize fingerprint serial at 57600 baud
  Serial.println("[1/3] Initializing fingerprint sensor at 57600 baud...");
  fingerSerial.begin(57600);
  delay(1000);
  
  finger.begin(57600);
  delay(500);
  
  Serial.println("✓ Fingerprint sensor initialized successfully");
  Serial.println("  Sensor communication established");
  
  // Setup WiFi
  Serial.println("\n[2/3] Connecting to WiFi...");
  setupWiFi();
  
  Serial.println("[3/3] System ready!");
  Serial.println("\n╔════════════════════════════════════════╗");
  Serial.println("║  FINGERPRINT BIOMETRIC SYSTEM ACTIVE   ║");
  Serial.println("╚════════════════════════════════════════╝\n");
  
  Serial.println("Available Commands:");
  Serial.println("  scan              - Normal fingerprint scan mode (time-in/out)");
  Serial.println("  enroll [staff_id] - Enroll new fingerprint (e.g., enroll 1)");
  Serial.println("  info              - Show system information");
  Serial.println("  status            - Check fingerprint sensor status");
  Serial.println("  help              - Show this help message");
  Serial.println("\nWaiting for commands...\n");
  
  blink(LED_OK_PIN, 5, 100);
}

// ============================================================
// MAIN LOOP
// ============================================================
void loop() {
  // Check WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠ WiFi disconnected! Reconnecting...");
    setupWiFi();
  }
  
  // Handle serial commands
  if (Serial.available() > 0) {
    handleSerialInput();
  }
  
  // Scan for fingerprints
  if (scanMode == 0) {
    scanFingerprint();
  } else if (scanMode == 1) {
    enrollFingerprint();
  }
  
  delay(500);
}

// ============================================================
// WIFI SETUP
// ============================================================
void setupWiFi() {
  Serial.print("  [WiFi] Attempting to connect to '");
  Serial.print(WIFI_SSID);
  Serial.println("'...");
  Serial.print("  [WiFi] Using password: ");
  Serial.println(WIFI_PASSWORD);
  
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  
  Serial.print("  [WiFi] Connection attempt: ");
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 40) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  Serial.println("");
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("  ✓ WiFi connected successfully!");
    Serial.print("  [WiFi] SSID: ");
    Serial.println(WiFi.SSID());
    Serial.print("  [WiFi] IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("  [WiFi] Gateway: ");
    Serial.println(WiFi.gatewayIP());
    Serial.print("  [WiFi] Signal Strength (RSSI): ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
    blink(LED_OK_PIN, 3, 150);
  } else {
    Serial.println("  ✗ WiFi connection FAILED!");
    Serial.println("  [WiFi] Troubleshooting:");
    Serial.print("    - Verify SSID '");
    Serial.print(WIFI_SSID);
    Serial.println("' is correct");
    Serial.print("    - Verify password '");
    Serial.print(WIFI_PASSWORD);
    Serial.println("' is correct");
    Serial.println("    - Check WiFi router is powered on");
    Serial.println("    - Ensure 2.4GHz network (not 5GHz)");
    Serial.println("    - Check if MAC address is blocked on router");
    blink(LED_ERR_PIN, 3, 150);
  }
}

// ============================================================
// FINGERPRINT SETUP
// ============================================================
void setupFingerprint() {
  Serial.println("\n--- Fingerprint Sensor Status ---");
  
  if (finger.getParameters()) {
    Serial.println("✓ Sensor is responding");
    Serial.print("  Capacity: ");
    Serial.print(finger.capacity);
    Serial.println(" templates");
    Serial.print("  Security Level: ");
    Serial.println(finger.security_level);
  } else {
    Serial.println("⚠ Could not read sensor parameters");
  }
  Serial.println("-------------------------------\n");
}

// ============================================================
// SCAN FINGERPRINT
// ============================================================
void scanFingerprint() {
  uint8_t p = finger.getImage();
  
  if (p == FINGERPRINT_NOFINGER) {
    return; // No finger detected, continue scanning
  } else if (p == FINGERPRINT_OK) {
    Serial.println("\n[SCAN] ✓ Fingerprint image captured");
    
    p = finger.image2Tz(1);
    if (p != FINGERPRINT_OK) {
      Serial.print("[SCAN] ✗ Failed to process image. Error code: ");
      Serial.println(p);
      blink(LED_ERR_PIN, 2, 100);
      return;
    }
    
    Serial.println("[SCAN] ✓ Image processed and converted to template");
    Serial.println("[SCAN] Searching database for match...");
    
    p = finger.fingerSearch();
    
    if (p == FINGERPRINT_OK) {
      Serial.print("[SCAN] ✓ MATCH FOUND!");
      Serial.print(" Fingerprint ID: ");
      Serial.print(finger.fingerID);
      Serial.print(" | Confidence: ");
      Serial.println(finger.confidence);
      
      // Send to server
      Serial.println("[SCAN] Sending time-in request to server...");
      sendTimeInRequest(finger.fingerID);
      blink(LED_OK_PIN, 5, 100);
      
      Serial.println("[SCAN] Waiting 2 seconds before next scan...");
      delay(2000);
    } else {
      Serial.println("[SCAN] ✗ No match found!");
      Serial.println("[SCAN] This fingerprint is not enrolled in the system");
      Serial.println("[SCAN] Ask admin to enroll this fingerprint");
      blink(LED_ERR_PIN, 1, 500);
      delay(2000);
    }
  } else {
    Serial.print("[SCAN] ✗ Sensor error code: ");
    Serial.println(p);
  }
}

// ============================================================
// ENROLL FINGERPRINT
// ============================================================
void enrollFingerprint() {
  Serial.println("\n╔════════════════════════════════════════╗");
  Serial.println("║       ENROLLMENT MODE STARTED          ║");
  Serial.println("╚════════════════════════════════════════╝");
  Serial.print("[ENROLL] Staff ID: ");
  Serial.println(enrollingStaffId);
  Serial.println("[ENROLL] Instructions: Place your finger on the sensor 2 times");
  Serial.println("\n");
  
  uint8_t p = 0xFF;
  
  // First image
  Serial.println("[ENROLL] STEP 1/4: First finger placement");
  Serial.println("[ENROLL] ▸ Place your finger on the sensor now...");
  while (p != FINGERPRINT_OK) {
    p = finger.getImage();
    if (p == FINGERPRINT_NOFINGER) {
      Serial.print(".");
      delay(500);
    } else if (p != FINGERPRINT_OK) {
      Serial.print("E");
      delay(500);
    }
  }
  
  Serial.println("\n[ENROLL] ✓ First image captured");
  
  p = finger.image2Tz(1);
  if (p != FINGERPRINT_OK) {
    Serial.print("[ENROLL] ✗ Failed to process first image. Error: ");
    Serial.println(p);
    enrollFingerprint();
    return;
  }
  
  Serial.println("[ENROLL] ✓ First image processed");
  
  // Wait for finger removal
  Serial.println("[ENROLL] STEP 2/4: Remove your finger");
  Serial.println("[ENROLL] ▸ Waiting 2 seconds...");
  delay(2000);
  Serial.println("[ENROLL] ✓ Finger removed");
  
  // Second image
  Serial.println("[ENROLL] STEP 3/4: Second finger placement");
  Serial.println("[ENROLL] ▸ Place your finger on the sensor again...");
  p = 0xFF;
  while (p != FINGERPRINT_OK) {
    p = finger.getImage();
    if (p == FINGERPRINT_NOFINGER) {
      Serial.print(".");
      delay(500);
    } else if (p != FINGERPRINT_OK) {
      Serial.print("E");
      delay(500);
    }
  }
  
  Serial.println("\n[ENROLL] ✓ Second image captured");
  
  p = finger.image2Tz(2);
  if (p != FINGERPRINT_OK) {
    Serial.print("[ENROLL] ✗ Failed to process second image. Error: ");
    Serial.println(p);
    enrollFingerprint();
    return;
  }
  
  Serial.println("[ENROLL] ✓ Second image processed");
  
  // Create model from two images
  Serial.println("[ENROLL] STEP 4/4: Creating fingerprint model...");
  p = finger.createModel();
  
  if (p == FINGERPRINT_OK) {
    Serial.println("[ENROLL] ✓ Fingerprint template created successfully");
    
    // Use staff ID as the fingerprint slot ID (unique per staff)
    uint16_t fingerprintId = enrollingStaffId;
    Serial.print("[ENROLL] ▸ Storing fingerprint at sensor slot ID: ");
    Serial.println(fingerprintId);
    
    // Store in sensor
    p = finger.storeModel(fingerprintId);
    if (p == FINGERPRINT_OK) {
      Serial.print("[ENROLL] ✓ Fingerprint stored in sensor successfully!");
      Serial.print(" (Slot: ");
      Serial.print(fingerprintId);
      Serial.println(")");
      
      // Send to server
      Serial.println("[ENROLL] ▸ Registering with server...");
      sendEnrollmentToServer(enrollingStaffId, fingerprintId);
      
      Serial.println("[ENROLL] ✓ Enrollment complete!");
      blink(LED_OK_PIN, 10, 100);
      
      scanMode = 0;
      enrollingStaffId = 0;
      delay(2000);
    } else {
      Serial.print("[ENROLL] ✗ Failed to store fingerprint in sensor. Error: ");
      Serial.println(p);
      blink(LED_ERR_PIN, 3, 200);
    }
  } else {
    Serial.print("[ENROLL] ✗ Failed to create fingerprint model. Error: ");
    Serial.println(p);
    blink(LED_ERR_PIN, 3, 200);
  }
}

// ============================================================
// SEND TIME-IN REQUEST TO SERVER
// ============================================================
void sendTimeInRequest(int fingerprintId) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[API] ✗ WiFi not connected! Cannot send request");
    Serial.println("[API] Reconnecting to WiFi...");
    setupWiFi();
    return;
  }
  
  Serial.println("[API] Preparing time-in request...");
  
  HTTPClient http;
  WiFiClient client;
  
  // Create JSON payload
  StaticJsonDocument<256> doc;
  doc["fingerprint_id"] = fingerprintId;
  doc["action"] = "time_in";
  doc["timestamp"] = String(__DATE__) + " " + String(__TIME__);
  
  String payload;
  serializeJson(doc, payload);
  
  String url = String(SERVER_HOST) + SERVER_BASE_PATH + API_TIMEIN_ENDPOINT;
  
  Serial.print("[API] Request URL: ");
  Serial.println(url);
  Serial.print("[API] Payload: ");
  Serial.println(payload);
  Serial.println("[API] Sending HTTP POST request...");
  
  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  
  int httpCode = http.POST(payload);
  String response = http.getString();
  
  Serial.print("[API] HTTP Response Code: ");
  Serial.println(httpCode);
  Serial.print("[API] Response Body: ");
  Serial.println(response);
  
  if (httpCode > 0) {
    if (httpCode == 200 || httpCode == 201) {
      Serial.println("[API] ✓ Request successful (200 OK)");
      
      // Parse response
      StaticJsonDocument<512> responseDoc;
      DeserializationError error = deserializeJson(responseDoc, response);
      
      if (!error) {
        bool success = responseDoc["success"] | false;
        String message = responseDoc["message"] | "No message";
        String status = responseDoc["status"] | "unknown";
        
        if (success) {
          Serial.print("[API] ✓ ");
          Serial.println(message);
          Serial.print("[API] Status: ");
          Serial.println(status);
          blink(LED_OK_PIN, 2, 120);
        } else {
          Serial.print("[API] ✗ ");
          Serial.println(message);
          blink(LED_ERR_PIN, 2, 120);
        }
      } else {
        Serial.println("[API] ⚠ Could not parse JSON response");
        blink(LED_ERR_PIN, 2, 120);
      }
    } else {
      Serial.print("[API] ⚠ HTTP Error: ");
      Serial.println(httpCode);
      blink(LED_ERR_PIN, 2, 120);
    }
  } else {
    Serial.print("[API] ✗ HTTP request failed. Error: ");
    Serial.println(http.errorToString(httpCode));
    blink(LED_ERR_PIN, 2, 120);
  }
  
  http.end();
}

// ============================================================
// SEND ENROLLMENT REQUEST TO SERVER
// ============================================================
void sendEnrollmentToServer(int staffId, int fingerprintId) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[API] ✗ WiFi not connected! Cannot register enrollment");
    Serial.println("[API] Reconnecting to WiFi...");
    setupWiFi();
    return;
  }
  
  Serial.println("[API] Preparing enrollment registration...");
  
  HTTPClient http;
  WiFiClient client;
  
  // Create JSON payload
  StaticJsonDocument<256> doc;
  doc["staff_id"] = staffId;
  doc["fingerprint_id"] = fingerprintId;
  doc["action"] = "enroll";
  
  String payload;
  serializeJson(doc, payload);
  
  String url = String(SERVER_HOST) + SERVER_BASE_PATH + API_ENROLL_ENDPOINT;
  
  Serial.print("[API] Request URL: ");
  Serial.println(url);
  Serial.print("[API] Payload: ");
  Serial.println(payload);
  Serial.println("[API] Sending HTTP POST request...");
  
  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  
  int httpCode = http.POST(payload);
  String response = http.getString();
  
  Serial.print("[API] HTTP Response Code: ");
  Serial.println(httpCode);
  Serial.print("[API] Response Body: ");
  Serial.println(response);
  
  if (httpCode > 0) {
    if (httpCode == 200 || httpCode == 201) {
      Serial.println("[API] ✓ Request successful (200 OK)");
      
      StaticJsonDocument<512> responseDoc;
      DeserializationError error = deserializeJson(responseDoc, response);
      
      if (!error) {
        bool success = responseDoc["success"] | false;
        String message = responseDoc["message"] | "No message";
        
        if (success) {
          Serial.print("[API] ✓ ");
          Serial.println(message);
          blink(LED_OK_PIN, 2, 120);
        } else {
          Serial.print("[API] ✗ ");
          Serial.println(message);
          blink(LED_ERR_PIN, 2, 120);
        }
      } else {
        Serial.println("[API] ⚠ Could not parse JSON response");
        blink(LED_ERR_PIN, 2, 120);
      }
    } else {
      Serial.print("[API] ⚠ HTTP Error: ");
      Serial.println(httpCode);
      blink(LED_ERR_PIN, 2, 120);
    }
  } else {
    Serial.print("[API] ✗ HTTP request failed. Error: ");
    Serial.println(http.errorToString(httpCode));
    blink(LED_ERR_PIN, 2, 120);
  }
  
  http.end();
}

// ============================================================
// HANDLE SERIAL INPUT COMMANDS
// ============================================================
void handleSerialInput() {
  String command = Serial.readStringUntil('\n');
  command.trim();
  command.toLowerCase();
  
  Serial.print("[CMD] Received command: '");
  Serial.print(command);
  Serial.println("'");
  
  if (command == "scan") {
    scanMode = 0;
    Serial.println("[CMD] ✓ Switched to SCAN mode");
    Serial.println("[CMD] ▸ Place your finger on the sensor to record time-in/out");
    Serial.println("");
    
  } else if (command.startsWith("enroll")) {
    // Parse: "enroll 5" (staff_id = 5)
    int spaceIndex = command.indexOf(' ');
    if (spaceIndex > 0) {
      String staffIdStr = command.substring(spaceIndex + 1);
      enrollingStaffId = staffIdStr.toInt();
      if (enrollingStaffId > 0) {
        scanMode = 1;
        Serial.print("[CMD] ✓ Switched to ENROLLMENT mode");
        Serial.print(" (Staff ID: ");
        Serial.print(enrollingStaffId);
        Serial.println(")");
      } else {
        Serial.println("[CMD] ✗ Invalid staff ID! Must be > 0");
      }
    } else {
      Serial.println("[CMD] ✗ Usage: enroll [staff_id]");
      Serial.println("[CMD]   Example: enroll 5");
    }
    
  } else if (command == "info") {
    Serial.println("\n╔════════════════════════════════════════╗");
    Serial.println("║       SYSTEM INFORMATION               ║");
    Serial.println("╠════════════════════════════════════════╣");
    Serial.print("║ WiFi Status: ");
    if (WiFi.status() == WL_CONNECTED) {
      Serial.print("Connected (");
      Serial.print(WiFi.localIP());
      Serial.println(") ║");
    } else {
      Serial.println("Disconnected                ║");
    }
    Serial.print("║ Current Mode: ");
    Serial.println(scanMode == 0 ? "SCAN (Time-in/out)         ║" : "ENROLL                      ║");
    Serial.print("║ Server: ");
    Serial.println(String(SERVER_HOST) + " ║");
    Serial.print("║ SSID: ");
    Serial.print(WIFI_SSID);
    Serial.println("                   ║");
    Serial.println("╚════════════════════════════════════════╝\n");
    
  } else if (command == "status") {
    Serial.println("[CMD] ✓ Checking fingerprint sensor status...");
    setupFingerprint();
    
  } else if (command == "help") {
    Serial.println("\n╔════════════════════════════════════════╗");
    Serial.println("║       AVAILABLE COMMANDS               ║");
    Serial.println("╚════════════════════════════════════════╝");
    Serial.println("[HELP] scan");
    Serial.println("[HELP]   └─ Switch to time-in/out scan mode");
    Serial.println("[HELP]");
    Serial.println("[HELP] enroll [staff_id]");
    Serial.println("[HELP]   └─ Start fingerprint enrollment");
    Serial.println("[HELP]   └─ Example: enroll 5");
    Serial.println("[HELP]");
    Serial.println("[HELP] info");
    Serial.println("[HELP]   └─ Display system information");
    Serial.println("[HELP]");
    Serial.println("[HELP] status");
    Serial.println("[HELP]   └─ Check fingerprint sensor status");
    Serial.println("[HELP]");
    Serial.println("[HELP] help");
    Serial.println("[HELP]   └─ Display this help message\n");
    
  } else if (command != "") {
    Serial.print("[CMD] ✗ Unknown command: '");
    Serial.print(command);
    Serial.println("'");
    Serial.println("[CMD] Type 'help' for available commands");
  }
}

// ============================================================
// LED BLINK FUNCTION
// ============================================================
void setLed(int pin, bool on) {
  bool outputHigh = LED_ACTIVE_LOW ? !on : on;
  digitalWrite(pin, outputHigh ? HIGH : LOW);
}

void runLedTest() {
  // Solid OK LED
  setLed(LED_OK_PIN, true);
  delay(LED_TEST_HOLD_MS);
  setLed(LED_OK_PIN, false);
  delay(300);

  // Solid ERR LED
  setLed(LED_ERR_PIN, true);
  delay(LED_TEST_HOLD_MS);
  setLed(LED_ERR_PIN, false);
  delay(300);

  // Both blink
  for (int i = 0; i < 3; i++) {
    setLed(LED_OK_PIN, true);
    setLed(LED_ERR_PIN, true);
    delay(200);
    setLed(LED_OK_PIN, false);
    setLed(LED_ERR_PIN, false);
    delay(200);
  }
}

void blink(int pin, int times, int delayMs) {
  for (int i = 0; i < times; i++) {
    setLed(pin, true);
    delay(delayMs);
    setLed(pin, false);
    delay(delayMs);
  }
}

// ============================================================
// END OF FIRMWARE
// ============================================================

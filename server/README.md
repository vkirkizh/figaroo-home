# Figaroo Home (2017-2020)

Custom smart home system built as a personal IoT project.

The system was designed around a Linux server running on Raspberry Pi and several custom hardware devices based on Arduino and ESP8266.

## Features

- Web dashboard for monitoring and controlling devices
- Smart lamp controlled via relay
- Infrared remote controller for air conditioner automation
- Climate monitoring station with temperature, humidity, and CO₂ sensors
- Integration with Apple Home via Homebridge

## Architecture

The project consisted of:

- Linux-based home server
- Raspberry Pi as the central controller
- Arduino / ESP8266-based devices
- Web interface for manual control and monitoring
- Homebridge integration for Apple Home support

## Devices

### Smart Lamp

Relay-based device for switching a lamp on and off remotely.

### IR Air Conditioner Controller

Infrared transmitter used to control an air conditioner from the web dashboard and Apple Home.

### Climate Station

Sensor-based device measuring:

- temperature
- humidity
- CO₂ level

## Why I built it

I built this project to explore IoT, embedded devices, home automation, and the integration between custom hardware and software systems.

It was a practical way to experiment with:

- device communication
- Linux-based automation
- hardware/software integration
- web dashboards
- smart home ecosystems

## Demo

### Hardware setup

![hardware setup](./pictures/hardware-demo.jpg)

### Mobile dashboard

![mobile dashboard](./pictures/mobile-dashboard.jpg)

---

Author: Valery Kirkizh (valery@kirkizh.com)

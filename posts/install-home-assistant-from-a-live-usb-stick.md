---
title: Install Home Assistant from a live USB stick
date: 2023-03-22
summary: How to actually install HASS from live Ubuntu to storage on the device itself
---

Recently I bought myself a thin client to do some tinkering and just short after my Raspberry Pi 4 (running Home Assistant) died. The SD-card probably wasn't happy with the huge amount of data written because I added [data from our P1-meter](https://www.zuidwijk.com/product/slimmelezer-plus/) at home. So instead of reinstalling HASS on the Raspberry Pi I opted using the thin client i bought. However installing HASS on it wasn't as easy as I'd hoped...

## At first

When following the [Generic x86-64 guide](https://www.home-assistant.io/installation/generic-x86-64) from the HASS website, they opt for installing Ubuntu on a USB-stick and booting from it and going into the live mode. The Raspberry Pi boots from an SD-card, so plopping it into a SD-card reader it can simply be flashed with HASS and you're done. But since I wanted to install HASS on the mSATA drive that's in the thin client, that wasn't an option. I found that some people actually put the drive into their day-to-day system and flash it like that, but mSATA is not very common so not an option for me. Getting a mSATA-to-USB converter for 20 euro's kind of bummed be out since it's another 20 bucks for a (hopefully) one time usage.

So I did as I was told and I installed Ubuntu on a USB-stick, configured UEFI as specified and booted from it and clicked 'Try Ubuntu'. I downloaded the HASS image and Balena Etcher. To get the latter running I needed to execute `sudo add-apt-repository universe`, `sudo apt-get install libfuse2` and set permissions to execute the file. After that Balena Etcher opened as it should, I could select the image and the mSATA drive and start flashing. 

# But then

As it was flashing to the mSATA drive, it would get stuck at either 27 of 36 percent, with the activity light on the USB-drive flashing manically! The system would become completely unresponsive and no matter how long I waited, it would not resume flashing anymore. After trying all kinds of things I found about it online, I just put it all aside for 2-3 weeks. Frustrated, sick and tired of it all!

# To the rescue

Today I decided to try again, maybe a new version of something would have fixed it. But allas, the same issue as before, no progress! 

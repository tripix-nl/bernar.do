---
title: Install Home Assistant from a live USB stick
date: 2023-03-23
summary: How to actually install HASS from live Ubuntu to storage on the device itself
---

Recently I bought myself a thin client to do some tinkering and just short after my Raspberry Pi 4 (running Home Assistant) died. The SD-card probably wasn't happy with the huge amount of data written because I added [data from our P1-meter](https://www.zuidwijk.com/product/slimmelezer-plus/) at home. So instead of reinstalling HASS on the Raspberry Pi I opted using the thin client i bought. However installing HASS on it wasn't as easy as I'd hoped...

## At first

When following the [Generic x86-64 guide](https://www.home-assistant.io/installation/generic-x86-64) from the HASS website, they opt for installing Ubuntu on a USB-stick and booting from it and going into the live mode. The Raspberry Pi boots from an SD-card, so plopping it into a SD-card reader it can simply be flashed with HASS and you're done. But since I wanted to install HASS on the mSATA drive that's in the thin client, that wasn't an option. I found that some people actually put the drive into their day-to-day system and flash it like that, but mSATA is not very common so not an option for me. Getting a mSATA-to-USB converter for 20 euro's kind of bummed be out since it's another 20 bucks for a (hopefully) one time usage.

So I did as I was told and I installed Ubuntu on a USB-stick, configured UEFI as specified and booted from it and clicked 'Try Ubuntu'. I downloaded the HASS image and Balena Etcher. To get the latter running I needed to execute `sudo add-apt-repository universe`, `sudo apt-get install libfuse2` and set permissions to execute the file. After that Balena Etcher opened as it should, I could select the image and the mSATA drive and start flashing. 

# But then

As it was flashing to the mSATA drive, it would get stuck at either 27 of 36 percent, with the activity light on the USB-drive flashing manically! The system would become completely unresponsive and no matter how long I waited, it would not resume flashing anymore. After trying all kinds of things I found about it online, I just put it all aside for 2-3 weeks. Frustrated, sick and tired of it all!

# To the rescue

Today I decided to try again, maybe a new version of something would have fixed it. But allas, the same issue as before, no progress...

Defeated and ready to throw in the towel I right clicked the HASS image which said something like 'open in disk image writer', which I did. Now I could again click the mSATA drive and after confirming it was simply flashing the HASS image to the new drive. No crashes, it just worked and it although it did not boot from it right away it did show up in the UEFI/BIOS. A [note in the HASS instal docs](https://www.home-assistant.io/installation/generic-x86-64#start-up-your-generic-x86-64) proved to be the solution and it just simply worked!

To summarize:

1. Create an Ubuntu live boot USB-stick: https://ubuntu.com/tutorials/try-ubuntu-before-you-install#1-getting-started;
2. Configure the UEFI/BIOS: https://www.home-assistant.io/installation/generic-x86-64#configure-the-bios-on-your-x86-64-hardware;
3. Boot from the Ubuntu USB-stick (in UEFI-mode) and select 'Try Ubuntu';
4. Open Firefox and download the latest release: https://www.home-assistant.io/installation/generic-x86-64#write-the-image-to-your-boot-medium or https://github.com/home-assistant/operating-system/releases/download/9.5/haos_generic-x86-64-9.5.img.xz;
5. Right click the image and follow the steps to write it to drive;
6. Run `efibootmgr --create --disk /dev/sda --part 1 --label "HAOS" --loader '\EFI\BOOT\bootx64.efi'` or check the note under https://www.home-assistant.io/installation/generic-x86-64#start-up-your-generic-x86-64;
7. Restart/reboot, remove Ubuntu USB-stick and Home Assistant should start! 

Anything I missed? Hit me up on Twitter: https://twitter.com/bernardohulsman

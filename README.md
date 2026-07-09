# Filebank

Watches a directory of files and after a file gets read once, replaces that file with a random one from the filebank. This way it is different every time it is read. Intended mostly for pre-rendering voice wav files with 200 different ways to say "job's done" giving every app that can play "done.wav" magic variation when it talks to you.

It does not care about the filetype though you could randomise anything like a directory of images, sounds, game textures, whatever.

## Structure

Example install directory:

* /opt/voicebank

Where banks of files are stored:

* /opt/voicebank/banks

The default bank called "default":

* /opt/voicebank/banks/default

With a choice of "done.wav" that can be randomised.

* /opt/voicebank/banks/default/done/001.wav
* /opt/voicebank/banks/default/done/002.wav
* /opt/voicebank/banks/default/done/003.wav
* /opt/voicebank/banks/default/done/004.wav

Filebank will populate this directory with the available choices by going
through the bank and choosing one of the files. The resulting file will be
named using the bank's folder name and the extension of the choice. Every time
this file then gets used by something, it will get replaced with a different
one from the bank so the read after that will be different.

* /opt/voicebank/files/done.wav

All the files in a bank folder should have the same extension, you would not
want to configure your programs for done.wav and have it choose 009.mp3 giving
you done.mp3 that would be dumb.

## Usage

Leave this running in the background.

`php server-php/server.php`

Once the PHP version is done and I am happy with how it works I intend to
rewrite it in C to be faster and lighter.

## Todo

* A lot of things still.

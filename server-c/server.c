////////////////////////////////////////////////////////////////////////////////
// W O R K - I N - P R O G R E S S /////////////////////////////////////////////

// use the php server for now. i built it there first so that all the time
// i spend relearning how to not crash in c doesnt derail me from just trying
// to archetect how i want things to work.

#include <stdio.h>
#include <unistd.h>
#include <limits.h>
#include <sys/poll.h>
#include <sys/inotify.h>

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

#define IN_EVENT_SIZE ( sizeof(struct inotify_event) + NAME_MAX + 1 )

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

void spin(int inServ) {

	int retval = -1;
	struct pollfd things[1];
	char buf[IN_EVENT_SIZE];
	struct inotify_event *ev;
	int readlen = 0;

	////////

	things[0].fd = inServ;
	things[0].events = POLLIN;

	////////

	while(1) {
		retval = poll(things, 1, -1);
		printf("poll says %d\n", retval);

		read(things[0].fd, buf, sizeof(buf));

		ev = (struct inotify_event *)buf;
		printf("%s\n", ev->name);

	}

	return;
};

int main(int argc, char *argv[]) {

	int inServ = 0;
	int inNode = 0;

	inServ = inotify_init1(IN_NONBLOCK);
	inNode = inotify_add_watch(inServ, "/opt/voicebank/files", IN_ALL_EVENTS);
	printf("inServ %d, inNode %d\n", inServ, inNode);

	spin(inServ);

	////////

	return 0;
};

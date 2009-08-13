TOPDIR := /home/root/work/kloxo

LIB_PATH := -L${TOPDIR}/lib -L/usr/lib/mysql

INCLUDE_PATH := -I ${TOPDIR}/sbin/src/include/

PACKAGEDIR = $(TOPDIR)/package
INSTALLDIR = $(PACKAGEDIR)/usr/local/kloxo

CFLAGS = $(INCLUDE_PATH) -g -w
CXXFLAGS = $(CFLAGS)
CC = g++

LDFLAGS = $(LIB_PATH) -lcrypt  -fpic -w 
VPATH = src



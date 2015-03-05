#!/usr/bin/python2.7
import os
import argparse
import urllib
import urllib2
import json
import getpass
from itertools import izip, imap

def main():
    parser = argparse.ArgumentParser()
    # currently urlopen doesn't check https certs, however see
    #  http://stackoverflow.com/questions/1087227/validate-ssl-certificates-with-python
    parser.add_argument('--url', default='http://10.7.0.193/catalog/api',
                        help='base url of the API')
    group = parser.add_argument_group('authentication')

    group.add_argument('--username', default=os.environ['USER'], help='username')
    group.add_argument('--password', default='', help='password')
    subparsers = parser.add_subparsers(dest='command',# help='commands',
                        title='commands',
                        description='valid commands')


    def add_command(func, help):
        cmd_parser = subparsers.add_parser(func.__name__, help=help)
        cmd_parser.set_defaults(func=func)
        return cmd_parser

    def make_request(url, other_args):

        other_args = [urllib.quote_plus(arg) for arg in other_args]


        #if args.password == '':
        #   args.password = getpass.getpass()
        #password = raw_input("Enter password:")
        #syntax is determined here, argparse doesn't process those
        rest = '/'.join(['username', args.username]+ ['password', args.password] + other_args)
        u = "%s%s" % (url, rest)

        try:
            return urllib2.urlopen(u).read()
        except Exception, e:
            return str(e)

    def json_request(url, other_args):
        try:
            return json.loads(make_request(url, other_args))
        except Exception, e:
            return {'flag': "Error decoding JSON", 'data':str(e)}

    def print_table(response):
        data = response['data']
        if response['flag'] != 'true':
            print(data)
            return

        lengths = [max(imap(len, column))
                   for column in izip(*data)]
        for row in data:
            print(' |'.join("%-*s" % (lengths[i], elem)
                           for i, elem in enumerate(row)  ))
            print('-+'.join("%-s" % ('-'.center(lengths[i],'-'))
                           for i, elem in enumerate(row)  ))

    # command: help
    def help(args, other_args):
        print(json_request("%s/help/" %args.url, [])['data'])
    add_command(help, help='retrieves the API help')

    # command: update
    def update(args, other_args):
        print(json_request("%s/update/" %args.url, other_args)['data'])
    add_command(update, help='retrieves updates')


    # command: list
    def list(args, other_args):
        d = json_request("%s/list/" % args.url, other_args)
        print_table(d)
    add_command(list, help='retrieves the list corresponding args from the API')

    # command: locks
    def locks(args, other_args):
        d = json_request("%s/locks/" % args.url, other_args)
        print_table(d)
    add_command(locks, help='retrieves the list of active locks for the corresponding args from the API')

    # command: lock
    def lock(args, other_args):
        d = json_request("%s/set-lock/" % args.url, other_args)
        print(d['data'])

    add_command(lock, help='set a lock via the API')

    # command: unlock
    def unlock(args, other_args):
        d = json_request("%s/set-unlock/" % args.url, other_args)
        print(d['data'])

    add_command(unlock, help='unset a lock via the API')


    args, other_args = parser.parse_known_args()
    args.func(args, other_args)

    return 0

    # command: qfree
    def qfree(args, other_args):
        d = json_request("%s/qfree/" % args.url, other_args)
        print(d['data'])

    add_command(qfree, help='unlock all via the API')

    args, other_args = parser.parse_known_args()
    args.func(args, other_args)

    return 0

if __name__ == '__main__':
    main()
(END)                          
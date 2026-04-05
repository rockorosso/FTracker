#!/usr/bin/env python3
"""
WealthTrack Server
Serves WealthTrack.html and handles data persistence by writing back to the HTML file.
Run with: python3 server.py
"""
import http.server
import json
import os
import re
import sys
from pathlib import Path

PORT = 8080
SCRIPT_DIR = Path(__file__).parent.resolve()
HTML_FILE = SCRIPT_DIR / 'WealthTrack.html'

DATA_START = '<!-- __WT_DATA_START__ -->'
DATA_END   = '<!-- __WT_DATA_END__ -->'
PASS_START = '<!-- __WT_PASS_START__ -->'
PASS_END   = '<!-- __WT_PASS_END__ -->'
USER_START      = '<!-- __WT_USER_START__ -->'
USER_END        = '<!-- __WT_USER_END__ -->'
CONT_USER_START = '<!-- __WT_CONT_USER_START__ -->'
CONT_USER_END   = '<!-- __WT_CONT_USER_END__ -->'
CONT_PASS_START = '<!-- __WT_CONT_PASS_START__ -->'
CONT_PASS_END   = '<!-- __WT_CONT_PASS_END__ -->'


def update_html(html: str, data=None, pass_hash=None, user_hash=None, cont_user_hash=None, cont_pass_hash=None) -> str:
    if data is not None:
        data_json = json.dumps(data, ensure_ascii=False, separators=(',', ':'))
        new_block = f'{DATA_START}<script>window.__WT_DATA__={data_json};</script>{DATA_END}'
        if DATA_START in html:
            html = re.sub(
                re.escape(DATA_START) + r'.*?' + re.escape(DATA_END),
                new_block, html, flags=re.DOTALL
            )
        else:
            html = html.replace('</head>', f'{new_block}\n</head>', 1)

    if pass_hash is not None:
        new_pass = f'{PASS_START}<script>window.__WT_PASS__="{pass_hash}";</script>{PASS_END}'
        if PASS_START in html:
            html = re.sub(
                re.escape(PASS_START) + r'.*?' + re.escape(PASS_END),
                new_pass, html, flags=re.DOTALL
            )
        else:
            html = html.replace('</head>', f'{new_pass}\n</head>', 1)

    if user_hash is not None:
        new_user = f'{USER_START}<script>window.__WT_USER__="{user_hash}";</script>{USER_END}'
        if USER_START in html:
            html = re.sub(
                re.escape(USER_START) + r'.*?' + re.escape(USER_END),
                new_user, html, flags=re.DOTALL
            )
        else:
            html = html.replace('</head>', f'{new_user}\n</head>', 1)

    if cont_user_hash is not None:
        new_cu = f'{CONT_USER_START}<script>window.__WT_CONT_USER__="{cont_user_hash}";</script>{CONT_USER_END}'
        if CONT_USER_START in html:
            html = re.sub(re.escape(CONT_USER_START)+r'.*?'+re.escape(CONT_USER_END), new_cu, html, flags=re.DOTALL)
        else:
            html = html.replace('</head>', f'{new_cu}\n</head>', 1)

    if cont_pass_hash is not None:
        new_cp = f'{CONT_PASS_START}<script>window.__WT_CONT_PASS__="{cont_pass_hash}";</script>{CONT_PASS_END}'
        if CONT_PASS_START in html:
            html = re.sub(re.escape(CONT_PASS_START)+r'.*?'+re.escape(CONT_PASS_END), new_cp, html, flags=re.DOTALL)
        else:
            html = html.replace('</head>', f'{new_cp}\n</head>', 1)

    return html


class WealthTrackHandler(http.server.SimpleHTTPRequestHandler):
    def do_OPTIONS(self):
        self.send_response(200)
        self._cors()
        self.end_headers()

    def do_POST(self):
        if self.path != '/save':
            self.send_error(404)
            return
        try:
            length = int(self.headers.get('Content-Length', 0))
            body = self.rfile.read(length)
            payload = json.loads(body)

            html = HTML_FILE.read_text(encoding='utf-8')
            html = update_html(
                html,
                data=payload.get('data'),
                pass_hash=payload.get('passHash'),
                user_hash=payload.get('userHash'),
                cont_user_hash=payload.get('contUserHash'),
                cont_pass_hash=payload.get('contPassHash')
            )
            HTML_FILE.write_text(html, encoding='utf-8')

            self.send_response(200)
            self._cors()
            self.send_header('Content-Type', 'application/json')
            self.end_headers()
            self.wfile.write(b'{"ok":true}')
        except Exception as e:
            self.send_response(500)
            self.end_headers()
            self.wfile.write(json.dumps({'error': str(e)}).encode())

    def _cors(self):
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')

    def log_message(self, fmt, *args):
        print(f'[WealthTrack] {self.address_string()} — {fmt % args}')


if __name__ == '__main__':
    os.chdir(SCRIPT_DIR)
    print(f'🚀  WealthTrack server → http://localhost:{PORT}/WealthTrack.html')
    print(f'📁  Data file: {HTML_FILE}')
    print(f'    Press Ctrl+C to stop.\n')
    with http.server.HTTPServer(('', PORT), WealthTrackHandler) as httpd:
        try:
            httpd.serve_forever()
        except KeyboardInterrupt:
            print('\n✅  Server stopped.')

import sys
import requests

NETWORKS=[
    'http://echo.atd3.org',  # 原始输出 hello world
    'http://think.atd3.org', # ThinkPHP
    'http://laravel.atd3.org'# Laravel
    'http://suda.atd3.org',  # Suda
]

class TestRequest:
    urls=None
    datas={}
    times=1
    current =0

    def __init__(self,times,urls):
        self.urls = urls
        self._init_datas()
        self.times=times

    def _get_process_time(self,url):
        return requests.get(url).elapsed.total_seconds()

    def _request_once(self):
        for url in  self.urls:
            time=self._get_process_time(url)
            self.datas[url].append(time)

    def _init_datas(self):
        for url in self.urls:
            self.datas[url]=[];

    def _get_avg(self,arr):
        if len(arr) <= 0 : return 0.0
        return sum(arr)/len(arr);

    def print_avg(self):
        print('[+] Summary')
        for url in  self.urls:
            print('    - request %s %d times in avg %fs, max %fs, min %fs' % (url,self.current,self._get_avg(self.datas[url]),max(self.datas[url]),min(self.datas[url])))

    def request_urls(self):
        width = 50
        print('[+] Processing')
        try:
            for i in range(self.times):
                self._request_once()
                self.current =  i+1
                progress = width * self.current / self.times
                text = '[-] |' + ('#' * int(progress) + '-' * int(width - progress)) +  ('| %3.0f%%'%(self.current/self.times * 100) +'\r')
                sys.stdout.write(' ' * (len(text) + 10) +'\r')
                sys.stdout.flush()
                sys.stdout.write(text)
                sys.stdout.flush()
        except KeyboardInterrupt:
            print()
            print('[!] Done');
            return    
        print()
        print('[+] Done');

if __name__ == '__main__':
    if len(sys.argv) < 2:
        times = 10
    else:
        times = int(sys.argv[1])
    test = TestRequest(times,NETWORKS)
    test.request_urls()
    test.print_avg()

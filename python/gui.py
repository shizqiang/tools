#!/bin/python
# -*- coding: utf-8 -*-  

import tkinter as tk

class Application(tk.Frame):
    def __init__(self, master=None):
        super().__init__(master)
        self.master = master
        self.grid()
        self.create_widgets()

    def create_widgets(self):
        self.hi_there = tk.Button(self)
        self.hi_there["text"] = "查询"
        self.hi_there["command"] = self.say_hi
        self.hi_there.grid(row=0, column=0)

        self.quit = tk.Button(self, text="退出", fg="red", command=self.master.destroy, width=6, height=2)
        self.quit.grid(row=0, column=1)

    def say_hi(self):
        print("hi there, everyone!")

root = tk.Tk()

# 设置标题
root.title("欢乐扫")
# 设置窗口大小和位置
width = 800
height = 480
#获取屏幕尺寸以计算布局参数，使窗口居屏幕中央
screenwidth = root.winfo_screenwidth()
screenheight = root.winfo_screenheight()
alignstr = '%dx%d+%d+%d' % (width, height, (screenwidth-width)/2, (screenheight-height)/2)
root.geometry(alignstr)

root.resizable(width=False, height=False)
root.iconbitmap('favicon.ico')
app = Application(master=root)

app.mainloop()
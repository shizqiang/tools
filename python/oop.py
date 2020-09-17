class Student:
    
    school = 'hahaha' # 可理解为静态变量
    
    def __init__(self, name = 'shizq', age = 18):
        self.name = name
        self.__age = age # 这个变量是私有的
        
    def getAge(self):
        return self.__age
        
s = Student()

class HuaHua(Student): # 继承
    pass

if __name__ == '__main__':
    print(s.getAge())
    print(isinstance(s, Student))
    print(HuaHua.school)
    h = HuaHua()
    print(h.getAge())
    print(type(1))
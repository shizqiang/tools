import asyncio

async def run():
    print('1')
    await asyncio.sleep(1)
    print('2')

async def main():
    await asyncio.gather(run(), run(), run())
    
asyncio.run(main())
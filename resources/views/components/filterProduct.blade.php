<div class="flex justify-evenly items-center w-full pb-16">

    <div class="flex justify-center items-center gap-8">
        <div class="flex justify-center items-center gap-3">
            <p class="font-Satoshi-bold">
                Matching Type:
            </p>

            <div class="flex justify-center items-center gap-2">
                <input type="radio" name="option" id="anyRadio" class="hidden">
                <label for="anyRadio" class="w-5 h-5 ring-1 ring-gray-400 rounded-md cursor-pointer"></label>
                
                <p id="anyText" class="font-Satoshi-bold text-base text-gray-400"> 
                    Any
                </p>
            </div>
            <div class="flex justify-center items-center gap-2">
                <input type="radio" name="option" id="allRadio" class="hidden">
                <label for="allRadio" class="w-5 h-5 ring-1 ring-gray-400 rounded-md cursor-pointer"></label>
                
                <p id="allText" class="font-Satoshi-bold text-base text-gray-400"> 
                    All
                </p>
            </div>
        </div>

        <div class="flex justify-center items-center gap-3">
            <p class="font-Satoshi-bold">
                Categories:
            </p>

            <div class="flex items-center gap-2 focus-within:text-white relative">
                <select name="select-categories" id="select-categories" class=" p-1 text-black border-black border rounded-md pl-2 pr-8 py-2">
                    <option value="">--All Categories--</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Books">Books</option>
                    <option value="Uniforms">Uniforms</option>
                    <option value="School Supplies">School Supplies</option>
                    <option value="Clothing">Clothing</option>
                    <option value="On Sale">On Sale</option>
                </select>

                <svg class="w-5 h-5 absolute right-2 pointer-events-none" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512">
                    <path d="M18.71,8.21a1,1,0,0,0-1.42,0l-4.58,4.58a1,1,0,0,1-1.42,0L6.71,8.21a1,1,0,0,0-1.42,0,1,1,0,0,0,0,1.41l4.59,4.59a3,3,0,0,0,4.24,0l4.59-4.59A1,1,0,0,0,18.71,8.21Z"/>
                </svg>
            </div>
        </div>
        
        <div class="flex justify-center items-center gap-3">
            <p class="font-Satoshi-bold">
                Price:
            </p>

            <div class="flex justify-center items-center gap-3 focus-within:text-white">
                <p class="font-Satoshi-bold text-base text-gray-400"> 
                    From
                </p>
                <input type="number" name="price" id="price" class="p-1 w-16 border-gray-400 border rounded-md text-black">
            </div>
            
            <div class="flex justify-center items-center gap-3 focus-within:text-white">
                <p class="font-Satoshi-bold text-base text-gray-400"> 
                    To
                </p>
                <input type="number" name="price" id="price" class="p-1 w-16 border-gray-400 border rounded-md text-black">
            </div>
        </div>    
    </div>
    <input type="submit" value="Search" class="py-1 px-4 border-black border rounded-md font-Satoshi-bold active:bg-black active:text-white active:border active:border-white active:transition-all">
</div>